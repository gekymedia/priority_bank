<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Loan;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentsController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->middleware('auth');
        $this->paymentService = $paymentService;
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
            ->where('is_group_loan', true)
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

        if ($request->payment_method === 'manual') {
            // For manual payments, create payment as pending (requires admin approval)
            $payment = Payment::create([
                'user_id' => Auth::id(),
                'loan_id' => $request->loan_id,
                'amount' => $request->amount,
                'payment_method' => 'manual',
                'status' => 'pending',
                'payment_date' => now(),
                'notes' => $request->notes,
            ]);

            return redirect()->route('payments.show', $payment->id)
                ->with('success', 'Manual payment submitted for approval. Admin will review your payment.');
        }

        // For gateway payments, initialize payment
        $reference = $this->paymentService->generateReference();

        $payment = Payment::create([
            'user_id' => Auth::id(),
            'loan_id' => $request->loan_id,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'transaction_reference' => $reference,
            'status' => 'pending',
            'payment_date' => now(),
            'notes' => $request->notes,
        ]);

        $user = Auth::user();

        // Initialize payment based on gateway
        if ($request->payment_method === 'paystack') {
            $paymentData = [
                'email' => $user->email,
                'amount' => $request->amount,
                'reference' => $reference,
                'callback_url' => route('payments.callback', ['gateway' => 'paystack']),
                'user_id' => $user->id,
                'loan_id' => $request->loan_id,
            ];

            $result = $this->paymentService->initializePaystackPayment($paymentData);

            if (isset($result['error'])) {
                $payment->update(['status' => 'failed']);
                return back()->withErrors(['payment' => $result['error']]);
            }

            return redirect($result['data']['authorization_url']);

        } elseif ($request->payment_method === 'hubtel') {
            $paymentData = [
                'email' => $user->email,
                'amount' => $request->amount,
                'reference' => $reference,
                'callback_url' => route('payments.callback', ['gateway' => 'hubtel']),
                'customer_name' => $user->name,
                'phone' => $user->phone,
                'user_id' => $user->id,
                'loan_id' => $request->loan_id,
            ];

            $result = $this->paymentService->initializeHubtelPayment($paymentData);

            if (isset($result['error'])) {
                $payment->update(['status' => 'failed']);
                return back()->withErrors(['payment' => $result['error']]);
            }

            return redirect($result['data']['checkoutUrl']);
        }

        return back()->withErrors(['payment' => 'Unsupported payment method']);
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

    /**
     * Handle payment gateway callback
     */
    public function callback(Request $request, string $gateway)
    {
        $reference = $request->query('reference') ?? $request->query('token');

        if (!$reference) {
            return redirect()->route('payments.index')->withErrors(['payment' => 'Invalid payment reference']);
        }

        // Find payment by reference
        $payment = Payment::where('transaction_reference', $reference)->first();

        if (!$payment) {
            return redirect()->route('payments.index')->withErrors(['payment' => 'Payment not found']);
        }

        // Verify payment with gateway
        if ($gateway === 'paystack') {
            $result = $this->paymentService->verifyPaystackPayment($reference);
        } elseif ($gateway === 'hubtel') {
            $result = $this->paymentService->verifyHubtelPayment($reference);
        } else {
            return redirect()->route('payments.index')->withErrors(['payment' => 'Invalid payment gateway']);
        }

        if ($result['success']) {
            // Process successful payment
            $processResult = $this->paymentService->processLoanRepayment($payment, $result);

            if ($processResult['success']) {
                return redirect()->route('payments.show', $payment->id)
                    ->with('success', 'Payment completed successfully!');
            } else {
                return redirect()->route('payments.show', $payment->id)
                    ->withErrors(['payment' => $processResult['message']]);
            }
        } else {
            $payment->update(['status' => 'failed']);
            return redirect()->route('payments.show', $payment->id)
                ->withErrors(['payment' => $result['message'] ?? 'Payment verification failed']);
        }
    }

    /**
     * Webhook handler for payment gateways
     */
    public function webhook(Request $request, string $gateway)
    {
        \Log::info("{$gateway} webhook received", $request->all());

        // Handle webhook based on gateway
        if ($gateway === 'paystack') {
            return $this->handlePaystackWebhook($request);
        } elseif ($gateway === 'hubtel') {
            return $this->handleHubtelWebhook($request);
        }

        return response()->json(['status' => 'error', 'message' => 'Invalid gateway'], 400);
    }

    /**
     * Handle Paystack webhook
     */
    private function handlePaystackWebhook(Request $request)
    {
        // Verify webhook signature (implement proper verification in production)
        $payload = $request->getContent();

        // For now, just process the event (implement signature verification in production)
        $event = json_decode($payload, true);

        if ($event && isset($event['event']) && $event['event'] === 'charge.success') {
            $reference = $event['data']['reference'];

            $payment = Payment::where('transaction_reference', $reference)->first();

            if ($payment && $payment->status === 'pending') {
                $result = $this->paymentService->verifyPaystackPayment($reference);

                if ($result['success']) {
                    $this->paymentService->processLoanRepayment($payment, $result);
                }
            }
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle Hubtel webhook
     */
    private function handleHubtelWebhook(Request $request)
    {
        // Handle Hubtel webhook (implement based on Hubtel webhook structure)
        $data = $request->all();

        if (isset($data['Status']) && $data['Status'] === 'completed') {
            $token = $data['Token'] ?? $data['token'];

            $payment = Payment::where('transaction_reference', $token)->first();

            if ($payment && $payment->status === 'pending') {
                $result = $this->paymentService->verifyHubtelPayment($token);

                if ($result['success']) {
                    $this->paymentService->processLoanRepayment($payment, $result);
                }
            }
        }

        return response()->json(['status' => 'success']);
    }
}
