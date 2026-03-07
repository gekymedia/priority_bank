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
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $query = Saving::with('user')->latest();
            if ($request->get('approval') === 'pending') {
                $query->pendingApproval();
            }
            $savings = $query->paginate(20)->withQueryString();
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
        $isOnlinePaymentAvailable = $this->paymentService->isOnlinePaymentAvailable();
        return view('savings.create', compact('isOnlinePaymentAvailable'));
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
                'status' => 'pending', // Pending until approved
            ]);

            return redirect()->route('savings.index')
                ->with('success', 'Deposit submitted for admin approval. Your deposit will be processed once verified.');
        }

        // Online payment (Paystack or Hubtel)
        $gateway = $paymentMethod; // Use the selected payment method directly
        
        // Validate that the selected gateway is available
        $paystackAvailable = !empty(config('services.paystack.secret_key'));
        $hubtelAvailable = !empty(config('services.hubtel.api_key') ?? config('services.hubtel.client_id')) && 
                          !empty(config('services.hubtel.api_secret') ?? config('services.hubtel.client_secret'));
        
        if ($gateway === 'paystack' && !$paystackAvailable) {
            return back()->withErrors(['payment' => 'Paystack payment is currently unavailable. Please use another payment method.']);
        }
        
        if ($gateway === 'hubtel' && !$hubtelAvailable) {
            return back()->withErrors(['payment' => 'Hubtel payment is currently unavailable. Please use another payment method.']);
        }
        
        if (!in_array($gateway, ['paystack', 'hubtel'])) {
            return back()->withErrors(['payment' => 'Invalid payment method selected.']);
        }

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
            'status' => 'pending',
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

        // Log the result for debugging
        \Illuminate\Support\Facades\Log::info('Payment initialization result', [
            'gateway' => $gateway,
            'result' => $result,
            'saving_id' => $saving->id
        ]);

        if (isset($result['error'])) {
            // Don't mark as failed immediately - keep as pending for 24 hours
            // Only show error to user but keep status as pending
            \Illuminate\Support\Facades\Log::error('Payment initialization error', [
                'error' => $result['error'],
                'saving_id' => $saving->id,
                'gateway' => $gateway
            ]);
            return back()->withErrors(['payment' => $result['error'] . ' Your deposit is still pending. You can try again later.']);
        }

        // Redirect to payment gateway (matching CUG pattern)
        if ($gateway === 'paystack') {
            // Paystack response structure: { status: true, data: { authorization_url: '...' } }
            if (isset($result['status']) && $result['status'] === true && isset($result['data']['authorization_url'])) {
                \Illuminate\Support\Facades\Log::info('Redirecting to Paystack', [
                    'url' => $result['data']['authorization_url'],
                    'reference' => $result['data']['reference'] ?? 'N/A',
                    'saving_id' => $saving->id
                ]);
                // Direct redirect like CUG does with ->redirectNow()
                return redirect($result['data']['authorization_url']);
            } else {
                \Illuminate\Support\Facades\Log::error('Paystack redirect URL not found', [
                    'result' => $result,
                    'status' => $result['status'] ?? 'not set',
                    'has_data' => isset($result['data']),
                    'has_auth_url' => isset($result['data']['authorization_url'])
                ]);
                // Don't mark as failed immediately - keep as pending for 24 hours
                return back()->withErrors(['payment' => 'Failed to initialize payment. Your deposit is still pending. You can try again later.']);
            }
        } else {
            // Hubtel response structure: { status: true, data: { checkoutUrl: '...' } } or { checkoutUrl: '...' }
            $redirectUrl = $result['data']['checkoutUrl'] ?? $result['checkoutUrl'] ?? null;
            if ($redirectUrl) {
                \Illuminate\Support\Facades\Log::info('Redirecting to Hubtel', [
                    'url' => $redirectUrl,
                    'saving_id' => $saving->id
                ]);
                return redirect($redirectUrl);
            } else {
                \Illuminate\Support\Facades\Log::error('Hubtel redirect URL not found', [
                    'result' => $result,
                    'saving_id' => $saving->id
                ]);
                // Don't mark as failed immediately - keep as pending for 24 hours
                return back()->withErrors(['payment' => 'Failed to initialize payment. Your deposit is still pending. You can try again later.']);
            }
        }
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
        // Get successful savings in order (oldest first)
        $availableSavings = Saving::where('user_id', $user->id)
            ->where('status', 'successful')
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
                    'status' => 'successful',
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
        // Allow user to view their own savings, or admin to view any
        if (!Auth::user()->isAdmin() && $saving->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access.');
        }
        return view('savings.show', compact('saving'));
    }

    /**
     * Retry payment for a pending online payment (Paystack/Hubtel).
     */
    public function retryPayment(Saving $saving)
    {
        // Only the owner can retry payment
        if ($saving->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access.');
        }

        // Only allow retry for pending online payments
        if (!in_array($saving->payment_method, ['paystack', 'hubtel']) || 
            $saving->approval_status !== 'pending' || 
            $saving->status !== 'pending') {
            return back()->withErrors(['payment' => 'This deposit cannot be paid at this time.']);
        }

        $gateway = $saving->payment_method;
        $user = $saving->user;

        // Use existing transaction reference or generate new one
        $reference = $saving->transaction_reference ?? $this->paymentService->generateReference();
        
        // Update reference if it was missing
        if (!$saving->transaction_reference) {
            $saving->update(['transaction_reference' => $reference]);
        }

        // Initialize payment
        $paymentData = [
            'email' => $user->email,
            'amount' => $saving->amount,
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

        // Log the result for debugging
        \Illuminate\Support\Facades\Log::info('Payment retry initialization result', [
            'gateway' => $gateway,
            'result' => $result,
            'saving_id' => $saving->id
        ]);

        if (isset($result['error'])) {
            return back()->withErrors(['payment' => $result['error']]);
        }

        // Redirect to payment gateway
        if ($gateway === 'paystack') {
            if (isset($result['status']) && $result['status'] === true && isset($result['data']['authorization_url'])) {
                \Illuminate\Support\Facades\Log::info('Redirecting to Paystack (retry)', [
                    'url' => $result['data']['authorization_url']
                ]);
                return redirect($result['data']['authorization_url']);
            } else {
                \Illuminate\Support\Facades\Log::error('Paystack redirect URL not found (retry)', [
                    'result' => $result
                ]);
                return back()->withErrors(['payment' => 'Failed to initialize payment. Please check your Paystack configuration.']);
            }
        } else {
            // Hubtel
            $redirectUrl = $result['data']['checkoutUrl'] ?? $result['checkoutUrl'] ?? null;
            if ($redirectUrl) {
                \Illuminate\Support\Facades\Log::info('Redirecting to Hubtel (retry)', ['url' => $redirectUrl]);
                return redirect($redirectUrl);
            } else {
                \Illuminate\Support\Facades\Log::error('Hubtel redirect URL not found (retry)', ['result' => $result]);
                return back()->withErrors(['payment' => 'Failed to initialize payment. Please check your Hubtel configuration.']);
            }
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Saving $saving)
    {
        // Allow user to edit their own savings, or admin to edit any
        if (!Auth::user()->isAdmin() && $saving->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access.');
        }
        return view('savings.edit', compact('saving'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Saving $saving)
    {
        // Allow user to update their own savings, or admin to update any
        if (!Auth::user()->isAdmin() && $saving->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'deposit_date' => 'required|date|before_or_equal:today',
            'status' => 'required|in:pending,successful,failed,withdrawn',
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
        // Allow user to delete their own savings, or admin to delete any
        if (!Auth::user()->isAdmin() && $saving->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access.');
        }

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

        // Verify payment with gateway (matching CUG pattern)
        if ($gateway === 'paystack') {
            $result = $this->paymentService->verifyPaystackPayment($reference);
            // Match CUG pattern: check status field
            $isSuccessful = ($result['status'] ?? false) === true && ($result['success'] ?? false) === true;
        } elseif ($gateway === 'hubtel') {
            $result = $this->paymentService->verifyHubtelPayment($reference);
            $isSuccessful = $result['success'] ?? false;
        } else {
            return redirect()->route('savings.index')->withErrors(['payment' => 'Invalid payment gateway']);
        }

        if ($isSuccessful) {
            // Process successful payment
            $processResult = $this->paymentService->processSavingsDeposit($saving, $result);

            if ($processResult['success']) {
                // Reload saving to get updated data
                $saving->refresh();
                
                // Create income transaction against Priority Bank source
                $priorityBank = \App\Models\SystemRegistry::where('system_id', 'priority_bank')->first();
                if ($priorityBank) {
                    \App\Models\Transaction::create([
                        'user_id' => $saving->user_id,
                        'type' => 'income',
                        'category' => 'Savings',
                        'amount' => $saving->amount,
                        'date' => $saving->deposit_date,
                        'description' => "Savings deposit from {$saving->user->name} - #{$saving->id} (Payment: {$gateway})",
                        'external_system_id' => $priorityBank->id,
                    ]);
                }
                
                // Automatically deduct from outstanding loans
                $this->processAutomaticLoanRepayment($saving);

                // Update group funds
                $groupFund = \App\Models\GroupFund::getInstance();
                $groupFund->updateTotals();

                // Notify the user about successful deposit
                $userNotificationService = new \App\Services\UserNotificationService();
                $userNotificationService->notifyDepositApproved(
                    $saving->user,
                    $saving->amount,
                    ucfirst($gateway)
                );

                return redirect()->route('savings.index')
                    ->with('success', 'Deposit completed successfully! ' . ($saving->status === 'withdrawn' ? 'Amount automatically applied to your outstanding loans.' : ''));
            } else {
                return redirect()->route('savings.index')
                    ->withErrors(['payment' => $processResult['message']]);
            }
        } else {
            $saving->update([
                'approval_status' => 'rejected',
                'status' => 'failed'
            ]);
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
            'status' => 'successful',
        ]);

        // Create income transaction against Priority Bank source (include deposit note for "view more")
        $priorityBank = \App\Models\SystemRegistry::where('system_id', 'priority_bank')->first();
        if ($priorityBank) {
            \App\Models\Transaction::create([
                'user_id' => $saving->user_id,
                'type' => 'income',
                'category' => 'Savings',
                'amount' => $saving->amount,
                'date' => $saving->deposit_date,
                'description' => "Savings deposit from {$saving->user->name} - #{$saving->id} (Direct Deposit)",
                'notes' => $saving->notes,
                'external_system_id' => $priorityBank->id,
            ]);
        }

        // Automatically deduct from outstanding loans
        $this->processAutomaticLoanRepayment($saving);

        // Update group funds
        $groupFund = GroupFund::getInstance();
        $groupFund->updateTotals();

        // Notify the user about deposit approval
        $userNotificationService = new \App\Services\UserNotificationService();
        $userNotificationService->notifyDepositApproved(
            $saving->user,
            $saving->amount,
            'Direct Deposit'
        );

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
            'status' => 'failed',
        ]);

        // Notify the user about deposit rejection
        $userNotificationService = new \App\Services\UserNotificationService();
        $userNotificationService->notifyDepositFailed(
            $saving->user,
            $saving->amount,
            'Deposit was rejected by admin'
        );

        return redirect()->route('savings.index')
            ->with('success', 'Deposit marked as failed.');
    }

    /**
     * Mark a deposit as failed (Admin only) - when MoMo transaction not found
     */
    public function markAsFailed(Saving $saving)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Only administrators can mark deposits as failed.');
        }

        if ($saving->status === 'successful' || $saving->status === 'withdrawn') {
            return back()->withErrors(['status' => 'Cannot mark a successful or withdrawn deposit as failed.']);
        }

        $saving->update([
            'approval_status' => 'rejected',
            'status' => 'failed',
        ]);

        // Notify the user about deposit failure
        $userNotificationService = new \App\Services\UserNotificationService();
        $userNotificationService->notifyDepositFailed(
            $saving->user,
            $saving->amount,
            'No transaction found in MoMo account. You can try again.'
        );

        return redirect()->route('savings.index')
            ->with('success', 'Deposit marked as failed. User can try again.');
    }

    /**
     * Try again - Change status from failed back to pending (User only)
     */
    public function tryAgain(Saving $saving)
    {
        // Only the owner can try again
        if ($saving->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access.');
        }

        // Only allow if status is failed
        if ($saving->status !== 'failed') {
            return back()->withErrors(['status' => 'This deposit cannot be retried.']);
        }

        $saving->update([
            'approval_status' => 'pending',
            'status' => 'pending',
        ]);

        return redirect()->route('savings.show', $saving)
            ->with('success', 'Deposit status reset to pending. Admin will review again.');
    }

    /**
     * Process automatic loan repayment from deposit
     */
    protected function processAutomaticLoanRepayment(Saving $saving)
    {
        $user = $saving->user;
        $depositAmount = $saving->amount;

        // Get user's outstanding group loans (oldest first)
        $outstandingLoans = \App\Models\Loan::where('user_id', $user->id)
            ->where('is_group_loan', true)
            ->where('status', 'borrowed')
            ->where('remaining_balance', '>', 0)
            ->orderBy('date_given', 'asc')
            ->get();

        if ($outstandingLoans->isEmpty()) {
            // No outstanding loans, deposit goes to savings
            return;
        }

        $remainingDeposit = $depositAmount;
        $loansRepaid = [];

        foreach ($outstandingLoans as $loan) {
            if ($remainingDeposit <= 0) {
                break;
            }

            $loanBalance = $loan->remaining_balance;
            $paymentAmount = min($remainingDeposit, $loanBalance);

            // Create payment record
            $payment = \App\Models\Payment::create([
                'user_id' => $user->id,
                'loan_id' => $loan->id,
                'amount' => $paymentAmount,
                'payment_method' => 'auto_deduction',
                'status' => 'completed',
                'payment_date' => $saving->deposit_date,
                'notes' => 'Automatic deduction from deposit - ' . ($saving->reference ? 'Ref: ' . $saving->reference : ''),
            ]);

            // Update loan balance
            $loan->updateRemainingBalance();

            $remainingDeposit -= $paymentAmount;
            $loansRepaid[] = [
                'loan_id' => $loan->id,
                'amount' => $paymentAmount,
                'remaining' => $loan->fresh()->remaining_balance
            ];
        }

        // If there's remaining deposit after paying all loans, it stays as savings
        // If deposit was fully used for loan repayment, mark saving as used
        if ($remainingDeposit < $depositAmount) {
            if ($remainingDeposit > 0) {
                // Partial: update saving amount to remaining
                $saving->update(['amount' => $remainingDeposit]);
            } else {
                // Full: mark as used for loan repayment
                $saving->update([
                    'status' => 'withdrawn',
                    'notes' => ($saving->notes ? $saving->notes . ' | ' : '') . 'Used for automatic loan repayment'
                ]);
            }
        }

        \Log::info('Automatic loan repayment processed', [
            'saving_id' => $saving->id,
            'user_id' => $user->id,
            'deposit_amount' => $depositAmount,
            'loans_repaid' => $loansRepaid,
            'remaining_deposit' => $remainingDeposit
        ]);
    }
}
