<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Income;
use App\Models\Expense;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoanController extends Controller
{
    public function index()
    {
        $loans = Loan::where('user_id', Auth::id())->with('account')->latest()->paginate(10);
        return view('loans.index', compact('loans'));
    }

    public function create()
    {
        $accounts = Account::where('user_id', Auth::id())->pluck('name', 'id');
        $channels = ['bank' => 'Bank', 'momo' => 'Mobile Money', 'cash' => 'Cash', 'other' => 'Other'];
        return view('loans.create', compact('accounts', 'channels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'borrower_name' => 'required|string|max:255',
            'borrower_phone' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date_given' => 'required|date',
            'expected_return_date' => 'nullable|date|after_or_equal:date_given',
            'channel' => 'required|in:bank,momo,cash,other',
            'account_id' => 'required|exists:accounts,id',
            'notes' => 'nullable|string',
        ]);

        Loan::create([
            'user_id' => Auth::id(),
            'borrower_name' => $request->borrower_name,
            'borrower_phone' => $request->borrower_phone,
            'amount' => $request->amount,
            'date_given' => $request->date_given,
            'expected_return_date' => $request->expected_return_date,
            'status' => 'borrowed',
            'returned_amount' => 0,
            'channel' => $request->channel,
            'account_id' => $request->account_id,
            'notes' => $request->notes,
        ]);

        return redirect()->route('loans.index')->with('success', 'Loan recorded successfully.');
    }

    public function edit(Loan $loan)
    {
        $this->authorize('update', $loan);
        $accounts = Account::where('user_id', Auth::id())->pluck('name', 'id');
        $channels = ['bank' => 'Bank', 'momo' => 'Mobile Money', 'cash' => 'Cash', 'other' => 'Other'];
        return view('loans.edit', compact('loan', 'accounts', 'channels'));
    }

    public function update(Request $request, Loan $loan)
    {
        $this->authorize('update', $loan);
        $request->validate([
            'borrower_name' => 'required|string|max:255',
            'borrower_phone' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date_given' => 'required|date',
            'expected_return_date' => 'nullable|date|after_or_equal:date_given',
            'channel' => 'required|in:bank,momo,cash,other',
            'account_id' => 'required|exists:accounts,id',
            'notes' => 'nullable|string',
        ]);

        $loan->update([
            'borrower_name' => $request->borrower_name,
            'borrower_phone' => $request->borrower_phone,
            'amount' => $request->amount,
            'date_given' => $request->date_given,
            'expected_return_date' => $request->expected_return_date,
            'channel' => $request->channel,
            'account_id' => $request->account_id,
            'notes' => $request->notes,
        ]);

        return redirect()->route('loans.index')->with('success', 'Loan updated successfully.');
    }

    public function destroy(Loan $loan)
    {
        $this->authorize('delete', $loan);
        $loan->delete();
        return redirect()->route('loans.index')->with('success', 'Loan deleted.');
    }

    /**
     * Mark a loan as returned. Creates an income entry for the returned amount and updates the loan.
     */
    public function markReturned(Request $request, Loan $loan)
    {
        $this->authorize('update', $loan);
        $request->validate([
            'returned_amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'account_id' => 'required|exists:accounts,id',
            'channel' => 'required|in:bank,momo,cash,other',
            'notes' => 'nullable|string',
        ]);

        // Update loan record
        $loan->status = 'returned';
        $loan->returned_amount = $request->returned_amount;
        $loan->notes = $loan->notes . ' | Returned: ' . $request->returned_amount;
        $loan->save();

        // Create corresponding income
        Income::create([
            'user_id' => $loan->user_id,
            'income_category_id' => null, // loan returns are a special category
            'account_id' => $request->account_id,
            'amount' => $request->returned_amount,
            'date' => $request->date,
            'channel' => $request->channel,
            'notes' => $request->notes ?? 'Loan return from ' . $loan->borrower_name,
        ]);

        return redirect()->route('loans.index')->with('success', 'Loan marked as returned.');
    }

    /**
     * Mark a loan as lost (bad debt). Creates an expense entry for the remaining unpaid portion.
     */
    public function markLost(Request $request, Loan $loan)
    {
        $this->authorize('update', $loan);
        $request->validate([
            'date' => 'required|date',
            'account_id' => 'required|exists:accounts,id',
            'channel' => 'required|in:bank,momo,cash,other',
            'notes' => 'nullable|string',
        ]);

        $remaining = $loan->amount - $loan->returned_amount;
        $loan->status = 'lost';
        $loan->notes = $loan->notes . ' | Lost';
        $loan->save();

        // Create expense for bad debt if remaining > 0
        if ($remaining > 0) {
            Expense::create([
                'user_id' => $loan->user_id,
                'expense_category_id' => null, // Loan loss special category
                'account_id' => $request->account_id,
                'amount' => $remaining,
                'date' => $request->date,
                'channel' => $request->channel,
                'notes' => $request->notes ?? 'Loan loss for ' . $loan->borrower_name,
            ]);
        }

        return redirect()->route('loans.index')->with('success', 'Loan marked as lost.');
    }
}
