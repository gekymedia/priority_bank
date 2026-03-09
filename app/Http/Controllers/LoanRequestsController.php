<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\LoanRequest;
use App\Models\Loan;
use App\Models\InterestRate;
use App\Models\GroupFund;
use App\Models\Transaction;
use App\Services\AdminNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoanRequestsController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $loanRequests = LoanRequest::with('user')->latest()->paginate(20);
        } else {
            $loanRequests = LoanRequest::where('user_id', $user->id)->latest()->paginate(20);
        }

        return view('loan-requests.index', compact('loanRequests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $interestRates = InterestRate::active()->forLoans()->get();
        $groupFund = GroupFund::getInstance();

        return view('loan-requests.create', compact('interestRates', 'groupFund'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'amount_requested' => 'required|numeric|min:1',
            'expected_payback_date' => 'required|date|after:today',
            'purpose' => 'nullable|string|max:500',
        ]);

        // No maximum restriction - admin will review and approve based on available funds

        $loanRequest = LoanRequest::create([
            'user_id' => Auth::id(),
            'amount_requested' => $request->amount_requested,
            'request_date' => now(),
            'expected_payback_date' => $request->expected_payback_date,
            'purpose' => $request->purpose,
            'status' => 'pending',
        ]);

        // Notify admins about the new loan request
        $notificationService = new AdminNotificationService();
        $user = Auth::user();
        $message = "New Loan Request\nUser: {$user->name} ({$user->email})\nAmount: GHS " . number_format($request->amount_requested, 2) . "\nExpected Payback: " . $request->expected_payback_date . "\nPurpose: " . ($request->purpose ?? 'N/A');
        $subject = "New Loan Request - Priority Savings Group";
        $notificationService->notifyAdmins($message, $subject);

        return redirect()->route('loan-requests.index')
            ->with('success', 'Loan request submitted successfully! It will be reviewed by the admin.');
    }

    /**
     * Display the specified resource.
     */
    public function show(LoanRequest $loanRequest)
    {
        // Allow admin to view any request, or user to view their own
        if (!Auth::user()->isAdmin() && $loanRequest->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access.');
        }
        $interestRates = InterestRate::active()->forLoans()->get();
        // Find or get 1% interest rate as default (MoMo charges/processing fee)
        $defaultInterestRate = InterestRate::active()->forLoans()->where('rate_percentage', 1.00)->first();
        if (!$defaultInterestRate) {
            // If 1% rate doesn't exist, get the first available rate
            $defaultInterestRate = $interestRates->first();
        }
        $groupFund = GroupFund::getInstance();
        $groupFund->updateTotals(); // ensure totals are current before showing
        $availableForLoans = (float) $groupFund->available_for_loans;
        // Admin can approve from 0 up to full group fund (may approve more than requested if deserved)
        return view('loan-requests.show', compact('loanRequest', 'interestRates', 'defaultInterestRate', 'groupFund', 'availableForLoans'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LoanRequest $loanRequest)
    {
        // Allow user to edit only their own pending requests
        if ($loanRequest->user_id !== Auth::id() || $loanRequest->status !== 'pending') {
            abort(403, 'Unauthorized access.');
        }
        $interestRates = InterestRate::active()->forLoans()->get();

        return view('loan-requests.edit', compact('loanRequest', 'interestRates'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LoanRequest $loanRequest)
    {
        // Allow user to update only their own pending requests
        if ($loanRequest->user_id !== Auth::id() || $loanRequest->status !== 'pending') {
            abort(403, 'Unauthorized access.');
        }

        if ($loanRequest->status !== 'pending') {
            return back()->withErrors(['status' => 'Cannot update a request that has already been processed.']);
        }

        $request->validate([
            'amount_requested' => 'required|numeric|min:1',
            'expected_payback_date' => 'required|date|after:today',
            'purpose' => 'nullable|string|max:500',
        ]);

        $loanRequest->update([
            'amount_requested' => $request->amount_requested,
            'expected_payback_date' => $request->expected_payback_date,
            'purpose' => $request->purpose,
        ]);

        return redirect()->route('loan-requests.index')
            ->with('success', 'Loan request updated successfully!');
    }

    /**
     * Approve loan request (Admin only).
     */
    public function approve(Request $request, LoanRequest $loanRequest)
    {
        // Only admins can approve
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Only administrators can approve loan requests.');
        }

        $groupFund = GroupFund::getInstance();
        $groupFund->updateTotals();
        $maxFromFund = (float) $groupFund->available_for_loans;

        $request->validate([
            'amount_approved' => 'required|numeric|min:1|max:' . $maxFromFund,
            'interest_rate_id' => 'required|exists:interest_rates,id',
        ]);

        $interestRate = InterestRate::findOrFail($request->interest_rate_id);

        // Update loan request
        $loanRequest->update([
            'amount_approved' => $request->amount_approved,
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        // Resolve account for disbursement (approver's first account or first in system)
        $account = Account::where('user_id', Auth::id())->first() ?? Account::first();
        if (!$account) {
            return redirect()->back()
                ->withErrors(['account' => 'No account exists. Please create an account first (e.g. under Income/Expense or Settings).']);
        }

        // Create the actual loan
        $totalWithInterest = $request->amount_approved + $interestRate->calculateInterest($request->amount_approved, 30);

        Loan::create([
            'user_id' => $loanRequest->user_id,
            'borrower_name' => $loanRequest->user->name,
            'borrower_phone' => $loanRequest->user->phone,
            'amount' => $request->amount_approved,
            'date_given' => now(),
            'disbursement_date' => now(),
            'expected_return_date' => $loanRequest->expected_payback_date,
            'status' => 'borrowed',
            'returned_amount' => 0,
            'remaining_balance' => $totalWithInterest,
            'channel' => 'bank',
            'account_id' => $account->id,
            'loan_request_id' => $loanRequest->id,
            'interest_rate_id' => $interestRate->id,
            'interest_rate_applied' => $interestRate->rate_percentage,
            'total_amount_with_interest' => $totalWithInterest,
            'loan_type' => 'personal',
            'is_group_loan' => true,
            'notes' => $loanRequest->purpose,
        ]);

        // Update group funds
        $groupFund->updateTotals();

        // Notify the user about loan approval using UserNotificationService
        $userNotificationService = new \App\Services\UserNotificationService();
        $userNotificationService->notifyLoanApproved(
            $loanRequest->user,
            $loanRequest->amount_requested,
            $request->amount_approved,
            $request->admin_notes
        );

        return redirect()->route('loan-requests.index')
            ->with('success', 'Loan request approved successfully! Use "Record Transaction as Paid" to disburse funds.');
    }

    /**
     * Reject loan request (Admin only).
     */
    public function reject(Request $request, LoanRequest $loanRequest)
    {
        // Only admins can reject
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Only administrators can reject loan requests.');
        }

        $loanRequest->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        // Notify the user about loan rejection
        $userNotificationService = new \App\Services\UserNotificationService();
        $userNotificationService->notifyLoanRejected(
            $loanRequest->user,
            $loanRequest->amount_requested,
            $request->admin_notes
        );

        return redirect()->route('loan-requests.index')
            ->with('success', 'Loan request rejected.');
    }

    /**
     * Record loan payment as transaction (Admin only).
     * Creates the loan record first if the request was approved but no loan exists (e.g. after a past error).
     */
    public function recordPayment(LoanRequest $loanRequest)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Only administrators can record loan payments.');
        }

        if ($loanRequest->status !== 'approved') {
            return back()->withErrors(['error' => 'Loan request must be approved before recording payment.']);
        }

        // If approved but no loan record (e.g. approval failed after updating status), create the loan now
        if (!$loanRequest->loan) {
            $groupFund = GroupFund::getInstance();
            $groupFund->updateTotals();
            $account = Account::where('user_id', Auth::id())->first() ?? Account::first();
            if (!$account) {
                return back()->withErrors(['error' => 'No account exists. Please create an account first (e.g. under Income/Expense or Settings).']);
            }
            $interestRate = InterestRate::active()->forLoans()->first();
            if (!$interestRate) {
                return back()->withErrors(['error' => 'No active interest rate found. Add one in settings.']);
            }
            $totalWithInterest = $loanRequest->amount_approved + $interestRate->calculateInterest($loanRequest->amount_approved, 30);
            Loan::create([
                'user_id' => $loanRequest->user_id,
                'borrower_name' => $loanRequest->user->name,
                'borrower_phone' => $loanRequest->user->phone,
                'amount' => $loanRequest->amount_approved,
                'date_given' => now(),
                'disbursement_date' => now(),
                'expected_return_date' => $loanRequest->expected_payback_date,
                'status' => 'borrowed',
                'returned_amount' => 0,
                'remaining_balance' => $totalWithInterest,
                'channel' => 'bank',
                'account_id' => $account->id,
                'loan_request_id' => $loanRequest->id,
                'interest_rate_id' => $interestRate->id,
                'interest_rate_applied' => $interestRate->rate_percentage,
                'total_amount_with_interest' => $totalWithInterest,
                'loan_type' => 'personal',
                'is_group_loan' => true,
                'notes' => $loanRequest->purpose,
            ]);
            $groupFund->updateTotals();
            $loanRequest->refresh();
        }

        // Check if transaction already exists (same borrower, loan disbursement for this request)
        $existingTransaction = Transaction::where('user_id', $loanRequest->user_id)
            ->where('type', 'expense')
            ->where('category', 'Loan Disbursement')
            ->where('description', 'like', "%Request #{$loanRequest->id}%")
            ->first();

        if ($existingTransaction) {
            return back()->withErrors(['error' => 'Transaction for this loan payment has already been recorded.']);
        }

        // Create expense transaction against the borrower (user who received the loan)
        Transaction::create([
            'user_id' => $loanRequest->user_id,
            'type' => 'expense',
            'category' => 'Loan Disbursement',
            'amount' => $loanRequest->amount_approved,
            'date' => now(),
            'description' => "Loan disbursement to {$loanRequest->user->name} - Request #{$loanRequest->id}",
            'external_system_id' => null,
        ]);

        if ($loanRequest->loan) {
            $loanRequest->loan->update(['disbursement_date' => now()]);
        }

        $userNotificationService = new \App\Services\UserNotificationService();
        $userNotificationService->notifyLoanPaymentRecorded(
            $loanRequest->user,
            $loanRequest->amount_approved,
            $loanRequest->loan ? $loanRequest->loan->remaining_balance : (float) $loanRequest->amount_approved
        );

        return redirect()->route('loan-requests.show', $loanRequest)
            ->with('success', 'Loan payment recorded as transaction successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LoanRequest $loanRequest)
    {
        // Allow user to delete only their own pending requests, or admin to delete any pending request
        if ($loanRequest->status !== 'pending') {
            return back()->withErrors(['status' => 'Cannot delete a request that has already been processed.']);
        }
        if (!Auth::user()->isAdmin() && $loanRequest->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access.');
        }

        if ($loanRequest->status !== 'pending') {
            return back()->withErrors(['status' => 'Cannot delete a request that has already been processed.']);
        }

        $loanRequest->delete();

        return redirect()->route('loan-requests.index')
            ->with('success', 'Loan request deleted successfully!');
    }
}
