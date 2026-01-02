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
        return view('expense.index', compact('expenses'));
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
        return view('expense.create', compact('categories', 'accounts', 'channels', 'systems'));
    }

    /**
     * Store a newly created expense in storage.
     */
    public function store(Request $request)
    {
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
        return view('expense.edit', compact('expense', 'categories', 'accounts', 'channels'));
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
        ]);

        $expense->update([
            'expense_category_id' => $request->expense_category_id,
            'account_id' => $request->account_id,
            'amount' => $request->amount,
            'date' => $request->date,
            'channel' => $request->channel,
            'notes' => $request->notes,
        ]);

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
