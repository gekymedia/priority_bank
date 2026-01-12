<?php

namespace App\Http\Controllers;

use App\Models\Saving;
use App\Models\GroupFund;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavingsController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $savings = Saving::with('user')->latest()->paginate(20);
        } else {
            $savings = Saving::where('user_id', $user->id)->latest()->paginate(20);
        }

        // Get payment gateway info for normal users
        $activeGateway = $this->paymentService->getActiveGateway();
        $isOnlinePaymentAvailable = $this->paymentService->isOnlinePaymentAvailable();

        return view('savings.index', compact('savings', 'activeGateway', 'isOnlinePaymentAvailable'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('savings.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'deposit_date' => 'required|date|before_or_equal:today',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
            'payment_method' => 'required|in:direct,paystack,hubtel',
        ]);

        $user = Auth::user();
        $paymentMethod = $request->payment_method;

        // Direct deposit (bank momo/cash) - requires admin approval
        if ($paymentMethod === 'direct') {
            $saving = Saving::create([
                'user_id' => $user->id,
                'amount' => $request->amount,
                'deposit_date' => $request->deposit_date,
                'reference' => $request->reference,
                'notes' => $request->notes,
                'payment_method' => 'direct',
                'approval_status' => 'pending',
                'status' => 'locked', // Locked until approved
            ]);

            return redirect()->route('savings.index')
                ->with('success', 'Deposit submitted for admin approval. Your deposit will be processed once verified.');
        }

        // Online payment (Paystack or Hubtel)
        $activeGateway = $this->paymentService->getActiveGateway();
        
        if (!$activeGateway) {
            return back()->withErrors(['payment' => 'Online payment is currently unavailable. Please use direct deposit.']);
        }

        // Use the active gateway (Hubtel takes precedence)
        $gateway = ($activeGateway === 'hubtel' && $paymentMethod === 'hubtel') ? 'hubtel' : 
                   (($activeGateway === 'paystack' && $paymentMethod === 'paystack') ? 'paystack' : $activeGateway);

        $reference = $this->paymentService->generateReference();

        // Create saving record with pending status
        $saving = Saving::create([
            'user_id' => $user->id,
            'amount' => $request->amount,
            'deposit_date' => $request->deposit_date,
            'reference' => $request->reference,
            'notes' => $request->notes,
            'payment_method' => $gateway,
            'approval_status' => 'pending',
            'status' => 'locked',
            'transaction_reference' => $reference,
        ]);

        // Initialize payment
        $paymentData = [
            'email' => $user->email,
            'amount' => $request->amount,
            'reference' => $reference,
            'callback_url' => route('savings.callback', ['gateway' => $gateway]),
            'customer_name' => $user->name,
            'phone' => $user->phone ?? '',
            'user_id' => $user->id,
            'saving_id' => $saving->id,
            'payment_type' => 'savings_deposit'
        ];

        if ($gateway === 'paystack') {
            $result = $this->paymentService->initializePaystackPayment($paymentData);
        } else {
            $result = $this->paymentService->initializeHubtelPayment($paymentData);
        }

        if (isset($result['error'])) {
            $saving->update(['approval_status' => 'rejected']);
            return back()->withErrors(['payment' => $result['error']]);
        }

        // Redirect to payment gateway
        $redirectUrl = $gateway === 'paystack' 
            ? $result['data']['authorization_url'] 
            : $result['data']['checkoutUrl'] ?? $result['checkoutUrl'];

        return redirect($redirectUrl);
    }

    /**
     * Withdraw money from savings.
     */
    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'withdraw_date' => 'required|date|before_or_equal:today',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $currentBalance = $user->savings_balance;
        $withdrawAmount = $request->amount;

        // Check if user has sufficient balance
        if ($currentBalance < $withdrawAmount) {
            // Create loan overdraft
            $overdraftAmount = $withdrawAmount - $currentBalance;
            
            // Withdraw available balance first
            if ($currentBalance > 0) {
                $this->processWithdrawal($user, $currentBalance, $request->withdraw_date, $request->reference, $request->notes);
            }
            
            // Create overdraft loan
            $loan = \App\Models\Loan::create([
                'user_id' => $user->id,
                'borrower_name' => $user->name,
                'amount' => $overdraftAmount,
                'date_given' => $request->withdraw_date,
                'status' => 'borrowed',
                'returned_amount' => 0,
                'remaining_balance' => $overdraftAmount,
                'is_group_loan' => true,
                'notes' => 'Loan Overdraft - ' . ($request->notes ?? 'Withdrawal exceeded available balance'),
            ]);

            // Update group funds
            $groupFund = GroupFund::getInstance();
            $groupFund->updateTotals();

            return redirect()->route('savings.index')
                ->with('warning', "Withdrawal processed. Available balance (GHS " . number_format($currentBalance, 2) . ") withdrawn. Remaining amount (GHS " . number_format($overdraftAmount, 2) . ") created as Loan Overdraft.");
        }

        // Normal withdrawal
        $this->processWithdrawal($user, $withdrawAmount, $request->withdraw_date, $request->reference, $request->notes);

        // Update group funds
        $groupFund = GroupFund::getInstance();
        $groupFund->updateTotals();

        return redirect()->route('savings.index')
            ->with('success', 'Withdrawal processed successfully!');
    }

    /**
     * Process withdrawal by marking savings as withdrawn.
     */
    protected function processWithdrawal($user, $amount, $date, $reference, $notes)
    {
        // Get available savings in order (oldest first)
        $availableSavings = Saving::where('user_id', $user->id)
            ->where('status', 'available')
            ->orderBy('deposit_date', 'asc')
            ->get();

        $remainingAmount = $amount;

        foreach ($availableSavings as $saving) {
            if ($remainingAmount <= 0) {
                break;
            }

            if ($saving->amount <= $remainingAmount) {
                // Mark entire saving as withdrawn
                $saving->update([
                    'status' => 'withdrawn',
                    'notes' => ($saving->notes ? $saving->notes . ' | ' : '') . 'Withdrawn: ' . ($notes ?? '') . ($reference ? ' (Ref: ' . $reference . ')' : ''),
                ]);
                $remainingAmount -= $saving->amount;
            } else {
                // Partial withdrawal - create new saving record for remaining amount
                $remainingSaving = $saving->amount - $remainingAmount;
                
                Saving::create([
                    'user_id' => $user->id,
                    'amount' => $remainingSaving,
                    'deposit_date' => $saving->deposit_date,
                    'status' => 'available',
                    'notes' => $saving->notes,
                ]);

                $saving->update([
                    'amount' => $remainingAmount,
                    'status' => 'withdrawn',
                    'notes' => ($saving->notes ? $saving->notes . ' | ' : '') . 'Withdrawn: ' . ($notes ?? '') . ($reference ? ' (Ref: ' . $reference . ')' : ''),
                ]);
                $remainingAmount = 0;
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Saving $saving)
    {
        $this->authorize('view', $saving);
        return view('savings.show', compact('saving'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Saving $saving)
    {
        $this->authorize('update', $saving);
        return view('savings.edit', compact('saving'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Saving $saving)
    {
        $this->authorize('update', $saving);

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'deposit_date' => 'required|date|before_or_equal:today',
            'status' => 'required|in:available,withdrawn,locked',
            'notes' => 'nullable|string|max:500',
        ]);

        $saving->update($request->only(['amount', 'deposit_date', 'status', 'notes']));

        // Update group funds
        $groupFund = GroupFund::getInstance();
        $groupFund->updateTotals();

        return redirect()->route('savings.index')
            ->with('success', 'Savings updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Saving $saving)
    {
        $this->authorize('delete', $saving);

        $saving->delete();

        // Update group funds
        $groupFund = GroupFund::getInstance();
        $groupFund->updateTotals();

        return redirect()->route('savings.index')
            ->with('success', 'Savings deleted successfully!');
    }

    /**
     * Handle payment gateway callback for savings deposits
     */
    public function callback(Request $request, string $gateway)
    {
        $reference = $request->query('reference') ?? $request->query('token');

        if (!$reference) {
            return redirect()->route('savings.index')->withErrors(['payment' => 'Invalid payment reference']);
        }

        // Find saving by reference
        $saving = Saving::where('transaction_reference', $reference)->first();

        if (!$saving) {
            return redirect()->route('savings.index')->withErrors(['payment' => 'Deposit not found']);
        }

        // Verify payment with gateway
        if ($gateway === 'paystack') {
            $result = $this->paymentService->verifyPaystackPayment($reference);
        } elseif ($gateway === 'hubtel') {
            $result = $this->paymentService->verifyHubtelPayment($reference);
        } else {
            return redirect()->route('savings.index')->withErrors(['payment' => 'Invalid payment gateway']);
        }

        if ($result['success']) {
            // Process successful payment
            $processResult = $this->paymentService->processSavingsDeposit($saving, $result);

            if ($processResult['success']) {
                // Reload saving to get updated data
                $saving->refresh();
                
                // Automatically deduct from outstanding loans
                $this->processAutomaticLoanRepayment($saving);

                // Update group funds
                $groupFund = \App\Models\GroupFund::getInstance();
                $groupFund->updateTotals();

                return redirect()->route('savings.index')
                    ->with('success', 'Deposit completed successfully! ' . ($saving->status === 'withdrawn' ? 'Amount automatically applied to your outstanding loans.' : ''));
            } else {
                return redirect()->route('savings.index')
                    ->withErrors(['payment' => $processResult['message']]);
            }
        } else {
            $saving->update(['approval_status' => 'rejected']);
            return redirect()->route('savings.index')
                ->withErrors(['payment' => $result['message'] ?? 'Payment verification failed']);
        }
    }

    /**
     * Approve a pending direct deposit (Admin only)
     */
    public function approve(Saving $saving)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        if ($saving->approval_status !== 'pending') {
            return back()->withErrors(['approval' => 'This deposit is not pending approval.']);
        }

        $saving->update([
            'approval_status' => 'approved',
            'status' => 'available',
        ]);

        // Automatically deduct from outstanding loans
        $this->processAutomaticLoanRepayment($saving);

        // Update group funds
        $groupFund = GroupFund::getInstance();
        $groupFund->updateTotals();

        return redirect()->route('savings.index')
            ->with('success', 'Deposit approved successfully!');
    }

    /**
     * Reject a pending direct deposit (Admin only)
     */
    public function reject(Saving $saving)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        if ($saving->approval_status !== 'pending') {
            return back()->withErrors(['approval' => 'This deposit is not pending approval.']);
        }

        $saving->update([
            'approval_status' => 'rejected',
        ]);

        return redirect()->route('savings.index')
            ->with('success', 'Deposit rejected.');
    }
}
