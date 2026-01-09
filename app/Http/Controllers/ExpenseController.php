<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Account;
use App\Models\SystemRegistry;
use App\Services\ExternalSystemWebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the user's expenses.
     */
    public function index()
    {
        $expenses = Expense::where('user_id', Auth::id())
            ->with(['category', 'account'])
            ->latest()->paginate(10);
        return view('expenses.index', compact('expenses'));
    }

    /**
     * Show the form for creating a new expense.
     */
    public function create()
    {
        $categories = ExpenseCategory::whereNull('user_id')
            ->orWhere('user_id', Auth::id())
            ->orderBy('name')
            ->pluck('name', 'id');
        $accounts = Account::where('user_id', Auth::id())->pluck('name', 'id');
        $channels = ['bank' => 'Bank', 'momo' => 'Mobile Money', 'cash' => 'Cash', 'other' => 'Other'];
        $systems = SystemRegistry::active()->orderBy('name')->pluck('name', 'id');
        return view('expenses.create', compact('categories', 'accounts', 'channels', 'systems'));
    }

    /**
     * Store a newly created expense in storage.
     */
    public function store(Request $request)
    {
        // Check if expenses array exists (multiple records) or single record
        if ($request->has('expenses') && is_array($request->expenses)) {
            // Handle multiple expense records
            $validated = $request->validate([
                'expenses' => 'required|array|min:1',
                'expenses.*.amount' => 'required|numeric|min:0',
                'expenses.*.expense_category_id' => 'required|exists:expense_categories,id',
                'expenses.*.date' => 'required|date',
                'expenses.*.channel' => 'required|in:bank,momo,cash,other',
                'expenses.*.account_id' => 'required|exists:accounts,id',
                'expenses.*.notes' => 'nullable|string',
                'expenses.*.external_system_id' => 'nullable|exists:systems_registry,id',
            ]);

            $savedCount = 0;
            foreach ($validated['expenses'] as $expenseData) {
                $expense = Expense::create([
                    'user_id' => Auth::id(),
                    'expense_category_id' => $expenseData['expense_category_id'],
                    'account_id' => $expenseData['account_id'],
                    'amount' => $expenseData['amount'],
                    'date' => $expenseData['date'],
                    'channel' => $expenseData['channel'],
                    'notes' => $expenseData['notes'] ?? null,
                    'external_system_id' => $expenseData['external_system_id'] ?? null,
                    'external_transaction_id' => isset($expenseData['external_system_id']) ? 'pb_' . time() . '_' . Auth::id() . '_' . $savedCount : null,
                    'sync_status' => isset($expenseData['external_system_id']) ? 'pending' : null,
                ]);

                // If external system is selected, push to that system
                if (isset($expenseData['external_system_id'])) {
                    $system = SystemRegistry::find($expenseData['external_system_id']);
                    if ($system && $system->callback_url) {
                        $webhookService = new ExternalSystemWebhookService();
                        $webhookService->pushExpense($expense, $system);
                    }
                }
                $savedCount++;
            }

            return redirect()->route('expenses.index')->with('success', $savedCount . ' expense record(s) recorded successfully.');
        } else {
            // Handle single expense record (backward compatibility)
            $request->validate([
                'amount' => 'required|numeric|min:0',
                'expense_category_id' => 'required|exists:expense_categories,id',
                'date' => 'required|date',
                'channel' => 'required|in:bank,momo,cash,other',
                'account_id' => 'required|exists:accounts,id',
                'notes' => 'nullable|string',
                'external_system_id' => 'nullable|exists:systems_registry,id',
            ]);

            $expense = Expense::create([
                'user_id' => Auth::id(),
                'expense_category_id' => $request->expense_category_id,
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
                    $webhookService->pushExpense($expense, $system);
                }
            }

            return redirect()->route('expenses.index')->with('success', 'Expense recorded successfully.');
        }
    }

    /**
     * Show the form for editing the specified expense.
     */
    public function edit(Expense $expense)
    {
        $this->authorize('update', $expense);
        $categories = ExpenseCategory::whereNull('user_id')
            ->orWhere('user_id', Auth::id())
            ->orderBy('name')
            ->pluck('name', 'id');
        $accounts = Account::where('user_id', Auth::id())->pluck('name', 'id');
        $channels = ['bank' => 'Bank', 'momo' => 'Mobile Money', 'cash' => 'Cash', 'other' => 'Other'];
        $systems = SystemRegistry::active()->orderBy('name')->pluck('name', 'id');
        return view('expenses.edit', compact('expense', 'categories', 'accounts', 'channels', 'systems'));
    }

    /**
     * Update the specified expense in storage.
     */
    public function update(Request $request, Expense $expense)
    {
        $this->authorize('update', $expense);
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'date' => 'required|date',
            'channel' => 'required|in:bank,momo,cash,other',
            'account_id' => 'required|exists:accounts,id',
            'notes' => 'nullable|string',
            'external_system_id' => 'nullable|exists:systems_registry,id',
        ]);

        $expense->update([
            'expense_category_id' => $request->expense_category_id,
            'account_id' => $request->account_id,
            'amount' => $request->amount,
            'date' => $request->date,
            'channel' => $request->channel,
            'notes' => $request->notes,
            'external_system_id' => $request->external_system_id,
        ]);

        // If external system is selected and changed, sync to that system
        if ($request->external_system_id && $expense->wasChanged('external_system_id')) {
            $system = SystemRegistry::find($request->external_system_id);
            if ($system && $system->callback_url) {
                $webhookService = new ExternalSystemWebhookService();
                $webhookService->pushExpense($expense, $system);
            }
        }

        return redirect()->route('expenses.index')->with('success', 'Expense updated successfully.');
    }

    /**
     * Remove the specified expense from storage.
     */
    public function destroy(Expense $expense)
    {
        $this->authorize('delete', $expense);

        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Expense deleted successfully.');
    }
}
