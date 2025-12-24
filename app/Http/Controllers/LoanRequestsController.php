<?php

namespace App\Http\Controllers;

use App\Models\LoanRequest;
use App\Models\Loan;
use App\Models\InterestRate;
use App\Models\GroupFund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoanRequestsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

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

        $groupFund = GroupFund::getInstance();

        // Check if sufficient funds are available
        if ($request->amount_requested > $groupFund->available_for_loans) {
            return back()->withErrors(['amount_requested' => 'Insufficient group funds available for this loan amount.'])
                        ->withInput();
        }

        LoanRequest::create([
            'user_id' => Auth::id(),
            'amount_requested' => $request->amount_requested,
            'request_date' => now(),
            'expected_payback_date' => $request->expected_payback_date,
            'purpose' => $request->purpose,
        ]);

        return redirect()->route('loan-requests.index')
            ->with('success', 'Loan request submitted successfully! It will be reviewed by the admin.');
    }

    /**
     * Display the specified resource.
     */
    public function show(LoanRequest $loanRequest)
    {
        $this->authorize('view', $loanRequest);
        return view('loan-requests.show', compact('loanRequest'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LoanRequest $loanRequest)
    {
        $this->authorize('update', $loanRequest);
        $interestRates = InterestRate::active()->forLoans()->get();

        return view('loan-requests.edit', compact('loanRequest', 'interestRates'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LoanRequest $loanRequest)
    {
        $this->authorize('update', $loanRequest);

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
        $this->authorize('approve', $loanRequest);

        $request->validate([
            'amount_approved' => 'required|numeric|min:1|max:' . $loanRequest->amount_requested,
            'interest_rate_id' => 'required|exists:interest_rates,id',
        ]);

        $interestRate = InterestRate::findOrFail($request->interest_rate_id);
        $groupFund = GroupFund::getInstance();

        // Check if sufficient funds are available
        if ($request->amount_approved > $groupFund->available_for_loans) {
            return back()->withErrors(['amount_approved' => 'Insufficient group funds available.']);
        }

        // Update loan request
        $loanRequest->update([
            'amount_approved' => $request->amount_approved,
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

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
            'loan_request_id' => $loanRequest->id,
            'interest_rate_id' => $interestRate->id,
            'interest_rate_applied' => $interestRate->rate_percentage,
            'total_amount_with_interest' => $totalWithInterest,
            'loan_type' => 'personal',
            'is_credit_union_loan' => true,
            'notes' => $loanRequest->purpose,
        ]);

        // Update group funds
        $groupFund->updateTotals();

        return redirect()->route('loan-requests.index')
            ->with('success', 'Loan request approved and loan disbursed successfully!');
    }

    /**
     * Reject loan request (Admin only).
     */
    public function reject(Request $request, LoanRequest $loanRequest)
    {
        $this->authorize('approve', $loanRequest);

        $loanRequest->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->route('loan-requests.index')
            ->with('success', 'Loan request rejected.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LoanRequest $loanRequest)
    {
        $this->authorize('delete', $loanRequest);

        if ($loanRequest->status !== 'pending') {
            return back()->withErrors(['status' => 'Cannot delete a request that has already been processed.']);
        }

        $loanRequest->delete();

        return redirect()->route('loan-requests.index')
            ->with('success', 'Loan request deleted successfully!');
    }
}
