<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Transaction;
use App\Models\Account;
use App\Models\User;
use App\Models\InterestRate;
use App\Models\GroupFund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoanController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        if ($user->isAdmin()) {
            // For admin, show Priority Bank transactions (income and expense from Priority Bank source)
            $priorityBankSource = \App\Models\SystemRegistry::where('system_id', 'priority_bank')->first();
            
            if ($priorityBankSource) {
                $transactions = \App\Models\Transaction::where('external_system_id', $priorityBankSource->id)
                    ->latest()
                    ->paginate(20);
                
                $totalIncome = \App\Models\Transaction::where('external_system_id', $priorityBankSource->id)
                    ->where('type', 'income')
                    ->sum('amount') ?? 0;
                
                $totalExpense = \App\Models\Transaction::where('external_system_id', $priorityBankSource->id)
                    ->where('type', 'expense')
                    ->sum('amount') ?? 0;
                
                return view('loans.index', compact('transactions', 'totalIncome', 'totalExpense', 'priorityBankSource'));
            }
            
            // Fallback to loans if source not found
            $loans = Loan::where('is_group_loan', true)
                ->with(['user', 'account'])
                ->latest()
                ->paginate(20);
            
            return view('loans.index', compact('loans'));
        } else {
            // Normal users see only their loans
            $loans = Loan::where('user_id', $user->id)
                ->where('is_group_loan', true)
                ->with('account')
                ->latest()
                ->paginate(20);
            
            return view('loans.index', compact('loans'));
        }
    }

    public function create()
    {
        $user = Auth::user();
        $interestRates = InterestRate::active()->forLoans()->get();
        $groupFund = GroupFund::getInstance();
        
        if ($user->isAdmin()) {
            // Admin can create loans for any user
            $users = User::where('status', 'approved')
                ->where('role', 'user')
                ->orderBy('name')
                ->pluck('name', 'id');
            $accounts = Account::pluck('name', 'id');
        } else {
            // Normal users can only create loans for themselves (but they should use loan-requests)
            $users = collect([$user->id => $user->name]);
            $accounts = Account::where('user_id', $user->id)->pluck('name', 'id');
        }
        
        $channels = ['bank' => 'Bank', 'momo' => 'Mobile Money', 'cash' => 'Cash', 'other' => 'Other'];
        
        return view('loans.create', compact('accounts', 'channels', 'users', 'interestRates', 'groupFund'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        $validationRules = [
            'amount' => 'required|numeric|min:1',
            'date_given' => 'required|date',
            'expected_return_date' => 'required|date|after_or_equal:date_given',
            'interest_rate_id' => 'required|exists:interest_rates,id',
            'notes' => 'nullable|string|max:500',
        ];
        
        if ($user->isAdmin()) {
            // Admin must select a user
            $validationRules['user_id'] = 'required|exists:users,id';
        }
        
        $request->validate($validationRules);
        
        // Get the target user (admin selects, or current user for normal users)
        $targetUserId = $user->isAdmin() ? $request->user_id : $user->id;
        $targetUser = User::findOrFail($targetUserId);
        
        // Check if sufficient funds are available (for group loans)
        $groupFund = GroupFund::getInstance();
        if ($request->amount > $groupFund->available_for_loans) {
            return back()->withErrors(['amount' => 'Insufficient group funds available for this loan amount.'])
                        ->withInput();
        }
        
        // Get interest rate
        $interestRate = InterestRate::findOrFail($request->interest_rate_id);
        
        // Calculate total with interest (assuming 30 days for calculation)
        $daysUntilReturn = now()->diffInDays($request->expected_return_date);
        $interestAmount = $interestRate->calculateInterest($request->amount, max(30, $daysUntilReturn));
        $totalWithInterest = $request->amount + $interestAmount;
        
        // Create the loan (automatically approved and disbursed)
        Loan::create([
            'user_id' => $targetUserId,
            'borrower_name' => $targetUser->name,
            'borrower_phone' => $targetUser->phone,
            'amount' => $request->amount,
            'date_given' => $request->date_given,
            'disbursement_date' => $request->date_given, // Money is assumed sent
            'expected_return_date' => $request->expected_return_date,
            'status' => 'borrowed', // Automatically approved and disbursed
            'returned_amount' => 0,
            'remaining_balance' => $totalWithInterest,
            'interest_rate_id' => $interestRate->id,
            'interest_rate_applied' => $interestRate->rate_percentage,
            'total_amount_with_interest' => $totalWithInterest,
            'loan_type' => 'personal',
            'is_group_loan' => true,
            'notes' => $request->notes ?? ($user->isAdmin() ? 'Loan created and approved by admin' : 'Loan created'),
        ]);
        
        // Update group funds
        $groupFund->updateTotals();
        
        $message = $user->isAdmin() 
            ? "Loan created and approved for {$targetUser->name}. Money is assumed to have been sent."
            : 'Loan recorded successfully.';
        
        return redirect()->route('loans.index')->with('success', $message);
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

        // Record loan return as income in transactions ledger
        Transaction::create([
            'user_id' => $loan->user_id,
            'type' => 'income',
            'category' => 'Loan return',
            'amount' => $request->returned_amount,
            'date' => $request->date,
            'description' => $request->notes ?? 'Loan return from ' . $loan->borrower_name,
            'notes' => null,
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

        // Record loan loss as expense in transactions ledger
        if ($remaining > 0) {
            Transaction::create([
                'user_id' => $loan->user_id,
                'type' => 'expense',
                'category' => 'Loan loss',
                'amount' => $remaining,
                'date' => $request->date,
                'description' => $request->notes ?? 'Loan loss for ' . $loan->borrower_name,
                'notes' => null,
            ]);
        }

        return redirect()->route('loans.index')->with('success', 'Loan marked as lost.');
    }
}
