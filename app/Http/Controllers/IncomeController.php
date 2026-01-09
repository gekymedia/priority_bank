<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\IncomeCategory;
use App\Models\Account;
use App\Models\SystemRegistry;
use App\Services\ExternalSystemWebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncomeController extends Controller
{
    /**
     * Display a listing of the user's incomes.
     */
    public function index()
    {
        $incomes = Income::where('user_id', Auth::id())
            ->with(['category', 'account'])
            ->latest()->paginate(10);
        return view('incomes.index', compact('incomes'));
    }

    /**
     * Show the form for creating a new income.
     */
    public function create()
    {
        // Fetch available categories (global or user-specific) and accounts
        $categories = IncomeCategory::whereNull('user_id')
            ->orWhere('user_id', Auth::id())
            ->orderBy('name')
            ->pluck('name', 'id');
        $accounts = Account::where('user_id', Auth::id())->pluck('name', 'id');
        $channels = ['bank' => 'Bank', 'momo' => 'Mobile Money', 'cash' => 'Cash', 'other' => 'Other'];
        $systems = SystemRegistry::active()->orderBy('name')->pluck('name', 'id');
        return view('incomes.create', compact('categories', 'accounts', 'channels', 'systems'));
    }

    /**
     * Store a newly created income in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'income_category_id' => 'required|exists:income_categories,id',
            'date' => 'required|date',
            'channel' => 'required|in:bank,momo,cash,other',
            'account_id' => 'required|exists:accounts,id',
            'notes' => 'nullable|string',
            'external_system_id' => 'nullable|exists:systems_registry,id',
        ]);

        $income = Income::create([
            'user_id' => Auth::id(),
            'income_category_id' => $request->income_category_id,
            'account_id' => $request->account_id,
            'amount' => $request->amount,
            'date' => $request->date,
            'channel' => $request->channel,
            'notes' => $request->notes,
            'external_system_id' => $request->external_system_id,
            'external_transaction_id' => $request->external_system_id ? 'pb_' . time() . '_' . Auth::id() : null,
            'sync_status' => $request->external_system_id ? 'pending' : null,
        ]);

        // If external system is selected, push to that system
        if ($request->external_system_id) {
            $system = SystemRegistry::find($request->external_system_id);
            if ($system && $system->callback_url) {
                $webhookService = new ExternalSystemWebhookService();
                $webhookService->pushIncome($income, $system);
            }
        }

        return redirect()->route('incomes.index')->with('success', 'Income recorded successfully.');
    }

    /**
     * Show the form for editing the specified income.
     */
    public function edit(Income $income)
    {
        $this->authorize('update', $income);
        $categories = IncomeCategory::whereNull('user_id')
            ->orWhere('user_id', Auth::id())
            ->orderBy('name')
            ->pluck('name', 'id');
        $accounts = Account::where('user_id', Auth::id())->pluck('name', 'id');
        $channels = ['bank' => 'Bank', 'momo' => 'Mobile Money', 'cash' => 'Cash', 'other' => 'Other'];
        return view('incomes.edit', compact('income', 'categories', 'accounts', 'channels'));
    }

    /**
     * Update the specified income in storage.
     */
    public function update(Request $request, Income $income)
    {
        $this->authorize('update', $income);
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'income_category_id' => 'required|exists:income_categories,id',
            'date' => 'required|date',
            'channel' => 'required|in:bank,momo,cash,other',
            'account_id' => 'required|exists:accounts,id',
            'notes' => 'nullable|string',
        ]);

        $income->update([
            'income_category_id' => $request->income_category_id,
            'account_id' => $request->account_id,
            'amount' => $request->amount,
            'date' => $request->date,
            'channel' => $request->channel,
            'notes' => $request->notes,
        ]);

        return redirect()->route('incomes.index')->with('success', 'Income updated successfully.');
    }

    /**
     * Remove the specified income from storage.
     */
    public function destroy(Income $income)
    {
        $this->authorize('delete', $income);

        $income->delete();

        return redirect()->route('incomes.index')->with('success', 'Income deleted successfully.');
    }
}
