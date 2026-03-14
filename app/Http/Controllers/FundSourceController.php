<?php

namespace App\Http\Controllers;

use App\Models\Saving;
use App\Models\Transaction;
use App\Models\SystemRegistry;
use App\Models\User;
use App\Models\FundTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FundSourceController extends Controller
{
    /**
     * Display a listing of fund sources.
     */
    public function index()
    {
        // Only admins can access fund sources
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $fundSources = [];

        // 1. Friends Savings Fund - Sum of all savings from non-admin users
        // Get all user IDs that are not admins
        $nonAdminUserIds = User::where(function($query) {
                $query->where('role', '!=', 'admin')
                      ->orWhereNull('role');
            })
            ->pluck('id')
            ->toArray();
        
        $friendsSavingsBalance = !empty($nonAdminUserIds) 
            ? Saving::whereIn('user_id', $nonAdminUserIds)
                ->where('status', 'successful')
                ->sum('amount')
            : 0;

        $fundSources[] = [
            'name' => 'Friends Savings Fund',
            'type' => 'savings',
            'balance' => $friendsSavingsBalance,
            'description' => 'Total available savings from all friends/members',
            'icon' => 'piggy-bank',
            'color' => 'blue'
        ];

        // 2. Get ALL source keys from systems_registry (with linked user for balance)
        $allSources = SystemRegistry::active()
            ->with('user')
            ->orderBy('name')
            ->get();

        // Color array for cycling through colors
        $colors = ['green', 'purple', 'indigo', 'pink', 'yellow', 'orange', 'teal'];
        $colorIndex = 0;

        foreach ($allSources as $system) {
            $hasLinkedUser = (bool) $system->user_id;
            $balance = null;
            $savings_balance = null;
            $loan_balance = null;
            $net_balance = null;
            $linked_user_name = null;
            $linked_user_id = null;

            if ($hasLinkedUser && $system->user) {
                $user = $system->user;
                $linked_user_name = $user->name;
                $linked_user_id = $user->id;
                $savings_balance = (float) $user->savings_balance;
                $loan_balance = (float) $user->loan_balance;
                $net_balance = (float) $user->net_balance;
                $balance = $net_balance;
            }

            $fundSources[] = [
                'name' => $system->name,
                'type' => 'api',
                'balance' => $balance,
                'system_id' => $system->id,
                'system_name' => $system->name,
                'description' => $system->description ?? 'Fund managed via API: ' . $system->name,
                'icon' => 'wallet',
                'color' => $colors[$colorIndex % count($colors)],
                'has_linked_user' => $hasLinkedUser,
                'linked_user_name' => $linked_user_name,
                'linked_user_id' => $linked_user_id,
                'savings_balance' => $savings_balance,
                'loan_balance' => $loan_balance,
                'net_balance' => $net_balance,
            ];

            $colorIndex++;
        }

        return view('fund-sources.index', compact('fundSources'));
    }

    /**
     * Transfer funds from admin fund to friends savings fund.
     */
    public function transfer(Request $request)
    {
        // Only admins can transfer funds
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'from_system_id' => 'required|exists:systems_registry,id',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $system = SystemRegistry::findOrFail($validated['from_system_id']);
            
            // Check if admin has enough balance in this fund using Transaction model
            $totalIncome = Transaction::where('external_system_id', $system->id)
                ->where('type', 'income')
                ->sum('amount');
            $totalExpenses = Transaction::where('external_system_id', $system->id)
                ->where('type', 'expense')
                ->sum('amount');
            $availableBalance = $totalIncome - $totalExpenses;

            if ($availableBalance < $validated['amount']) {
                return back()->withErrors(['amount' => 'Insufficient balance in this fund. Available: GHS ' . number_format($availableBalance, 2)]);
            }

            // Create expense transaction record in admin's API fund using Transaction model
            $expenseTransaction = Transaction::create([
                'user_id' => Auth::id(),
                'type' => 'expense',
                'category' => 'Fund Transfer',
                'amount' => $validated['amount'],
                'date' => now(),
                'description' => 'Fund Transfer to Friends Savings Fund' . ($validated['notes'] ? ' - ' . $validated['notes'] : ''),
                'external_system_id' => $system->id,
            ]);

            // Create fund transfer record
            $fundTransfer = FundTransfer::create([
                'from_fund_type' => 'api',
                'from_system_id' => $system->id,
                'to_fund_type' => 'savings',
                'to_system_id' => null,
                'amount' => $validated['amount'],
                'notes' => $validated['notes'],
                'expense_id' => $expenseTransaction->id,
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return redirect()->route('admin.fund-sources.index')
                ->with('success', 'Funds transferred successfully! GHS ' . number_format($validated['amount'], 2) . ' transferred from ' . $system->name . ' to Friends Savings Fund.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Transfer failed: ' . $e->getMessage()]);
        }
    }
}
