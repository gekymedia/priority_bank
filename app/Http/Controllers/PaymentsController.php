<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentsController extends Controller
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
            $payments = Payment::with(['user', 'loan'])->latest()->paginate(20);
        } else {
            $payments = Payment::where('user_id', $user->id)->with('loan')->latest()->paginate(20);
        }

        return view('payments.index', compact('payments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        // Get user's outstanding loans
        $loans = $user->loans()
            ->where('is_credit_union_loan', true)
            ->where('status', 'borrowed')
            ->where('remaining_balance', '>', 0)
            ->get();

        // If loan_id is specified in query, pre-select it
        $selectedLoan = null;
        if ($request->has('loan_id')) {
            $selectedLoan = $loans->find($request->loan_id);
        }

        return view('payments.create', compact('loans', 'selectedLoan'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'loan_id' => 'required|exists:loans,id',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:paystack,hubtel,manual',
            'notes' => 'nullable|string|max:500',
        ]);

        $loan = Loan::findOrFail($request->loan_id);

        // Verify loan belongs to user and is outstanding
        if ($loan->user_id !== Auth::id() || $loan->status !== 'borrowed' || $loan->remaining_balance <= 0) {
            return back()->withErrors(['loan_id' => 'Invalid loan selected.']);
        }

        // Check if payment amount is reasonable (not more than remaining balance + some buffer)
        if ($request->amount > $loan->remaining_balance * 1.5) {
            return back()->withErrors(['amount' => 'Payment amount seems too high for the remaining balance.']);
        }

        // For now, create payment as completed (in production, this would integrate with payment gateways)
        $payment = $loan->makePayment($request->amount, $request->payment_method, $request->notes);

        return redirect()->route('payments.show', $payment->id)
            ->with('success', 'Payment recorded successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment)
    {
        $this->authorize('view', $payment);
        return view('payments.show', compact('payment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payment $payment)
    {
        $this->authorize('update', $payment);
        return view('payments.edit', compact('payment'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payment $payment)
    {
        $this->authorize('update', $payment);

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:paystack,hubtel,manual',
            'status' => 'required|in:pending,completed,failed,refunded',
            'notes' => 'nullable|string|max:500',
        ]);

        $payment->update($request->only(['amount', 'payment_method', 'status', 'notes']));

        // If payment status changed to completed, update loan balance
        if ($request->status === 'completed' && $payment->status !== 'completed') {
            $payment->loan->updateRemainingBalance();
        }

        return redirect()->route('payments.index')
            ->with('success', 'Payment updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        $this->authorize('delete', $payment);

        // Don't allow deletion of completed payments that have affected loan balances
        if ($payment->status === 'completed') {
            return back()->withErrors(['payment' => 'Cannot delete completed payments.']);
        }

        $payment->delete();

        return redirect()->route('payments.index')
            ->with('success', 'Payment deleted successfully!');
    }
}
