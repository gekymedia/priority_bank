<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Loan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected $paystackSecretKey;
    protected $hubtelApiKey;
    protected $hubtelApiSecret;

    public function __construct()
    {
        $this->paystackSecretKey = config('services.paystack.secret_key');
        $this->hubtelApiKey = config('services.hubtel.api_key') ?? config('services.hubtel.client_id');
        $this->hubtelApiSecret = config('services.hubtel.api_secret') ?? config('services.hubtel.client_secret');
    }

    /**
     * Determine which payment gateway to use (Hubtel takes precedence)
     */
    public function getActiveGateway(): ?string
    {
        // Hubtel takes precedence if both are configured
        if (!empty($this->hubtelApiKey) && !empty($this->hubtelApiSecret)) {
            return 'hubtel';
        }
        
        if (!empty($this->paystackSecretKey)) {
            return 'paystack';
        }
        
        return null;
    }

    /**
     * Check if online payment is available
     */
    public function isOnlinePaymentAvailable(): bool
    {
        return $this->getActiveGateway() !== null;
    }

    /**
     * Initialize Paystack payment
     * Returns authorization URL for redirect (similar to CUG implementation)
     */
    public function initializePaystackPayment(array $data)
    {
        try {
            // Generate reference if not provided (like CUG does)
            $reference = $data['reference'] ?? $this->generateReference();
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->paystackSecretKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.paystack.co/transaction/initialize', [
                'email' => $data['email'],
                'amount' => $data['amount'] * 100, // Paystack expects amount in kobo (pesewas)
                'currency' => 'GHS',
                'reference' => $reference,
                'callback_url' => $data['callback_url'],
                'metadata' => array_merge([
                    'loan_id' => $data['loan_id'] ?? null,
                    'saving_id' => $data['saving_id'] ?? null,
                    'deposit_id' => $data['deposit_id'] ?? null,
                    'user_id' => $data['user_id'],
                    'payment_type' => $data['payment_type'] ?? 'loan_repayment'
                ], $data['metadata'] ?? [])
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                // Verify the response structure (matching CUG pattern)
                if (isset($result['status']) && $result['status'] === true && isset($result['data']['authorization_url'])) {
                    Log::info('Paystack payment initialized successfully', [
                        'authorization_url' => $result['data']['authorization_url'],
                        'reference' => $result['data']['reference'] ?? $reference
                    ]);
                    
                    // Return in format similar to CUG's Paystack package
                    return [
                        'status' => true,
                        'data' => [
                            'authorization_url' => $result['data']['authorization_url'],
                            'reference' => $result['data']['reference'] ?? $reference
                        ]
                    ];
                } else {
                    Log::error('Paystack response structure invalid', [
                        'response' => $result,
                        'has_status' => isset($result['status']),
                        'status_value' => $result['status'] ?? 'not set',
                        'has_data' => isset($result['data']),
                        'has_auth_url' => isset($result['data']['authorization_url'])
                    ]);
                    return ['error' => 'Invalid response from payment gateway. Please check your Paystack configuration.'];
                }
            }

            $errorResponse = $response->json();
            Log::error('Paystack initialization failed', [
                'response' => $response->body(),
                'status' => $response->status(),
                'error_message' => $errorResponse['message'] ?? 'Unknown error'
            ]);

            return ['error' => 'Payment initialization failed: ' . ($errorResponse['message'] ?? 'Unknown error')];

        } catch (\Exception $e) {
            Log::error('Paystack payment initialization error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);

            return ['error' => 'Payment service temporarily unavailable: ' . $e->getMessage()];
        }
    }

    /**
     * Verify Paystack payment (matching CUG pattern)
     * Returns data in format similar to CUG's $this->paystack->getPaymentData()
     */
    public function verifyPaystackPayment(string $reference)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->paystackSecretKey,
            ])->get("https://api.paystack.co/transaction/verify/{$reference}");

            if ($response->successful()) {
                $data = $response->json();

                // Match CUG's pattern: check if status is true and data exists
                if (isset($data['status']) && $data['status'] === true && isset($data['data'])) {
                    $paymentData = $data['data'];
                    
                    // Check if payment was successful
                    if ($paymentData['status'] === 'success') {
                    return [
                            'status' => true,
                        'success' => true,
                            'data' => $paymentData,
                            'amount' => $paymentData['amount'] / 100, // Convert from kobo to cedis
                            'reference' => $paymentData['reference'],
                            'metadata' => $paymentData['metadata'] ?? []
                    ];
                    } else {
                        return [
                            'status' => false,
                            'success' => false,
                            'message' => 'Payment not successful. Status: ' . ($paymentData['status'] ?? 'unknown')
                        ];
                    }
            }

                return [
                    'status' => false,
                    'success' => false,
                    'message' => 'Invalid response structure from Paystack'
                ];
            }

            return [
                'status' => false,
                'success' => false,
                'message' => 'Verification failed. HTTP Status: ' . $response->status()
            ];

        } catch (\Exception $e) {
            Log::error('Paystack verification error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'reference' => $reference
            ]);

            return [
                'status' => false,
                'success' => false,
                'message' => 'Verification service unavailable: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Initialize Hubtel payment
     */
    public function initializeHubtelPayment(array $data)
    {
        try {
            $auth = base64_encode($this->hubtelApiKey . ':' . $this->hubtelApiSecret);

            $paymentType = $data['payment_type'] ?? 'loan_repayment';
            $itemName = $paymentType === 'savings_deposit' ? 'Savings Deposit' : 'Loan Repayment';
            $description = $paymentType === 'savings_deposit' ? 'Credit Union Savings Deposit' : 'Credit Union Loan Repayment';

            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $auth,
                'Content-Type' => 'application/json',
            ])->post('https://api.hubtel.com/v2/pos/onlinecheckout/items/initiate', [
                'invoice' => [
                    'items' => [
                        [
                            'name' => $itemName,
                            'quantity' => 1,
                            'unitPrice' => $data['amount'],
                            'totalPrice' => $data['amount'],
                            'description' => $description
                        ]
                    ],
                    'totalAmount' => $data['amount'],
                    'description' => $description,
                    'customerName' => $data['customer_name'] ?? 'Customer',
                    'customerMsisdn' => $data['phone'] ?? '',
                    'customerEmail' => $data['email'],
                    'channel' => 'card',
                    'token' => $data['reference'],
                    'callbackUrl' => $data['callback_url']
                ]
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Hubtel initialization failed', [
                'response' => $response->body(),
                'status' => $response->status()
            ]);

            return ['error' => 'Payment initialization failed'];

        } catch (\Exception $e) {
            Log::error('Hubtel payment initialization error', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return ['error' => 'Payment service temporarily unavailable'];
        }
    }

    /**
     * Verify Hubtel payment
     */
    public function verifyHubtelPayment(string $token)
    {
        try {
            $auth = base64_encode($this->hubtelApiKey . ':' . $this->hubtelApiSecret);

            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $auth,
            ])->get("https://api.hubtel.com/v2/pos/onlinecheckout/items/status/{$token}");

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['status']) && $data['status'] === 'completed') {
                    return [
                        'success' => true,
                        'data' => $data,
                        'amount' => $data['amount'] ?? 0,
                        'reference' => $data['token'] ?? $token,
                        'transaction_id' => $data['transactionId'] ?? null
                    ];
                }

                return ['success' => false, 'message' => 'Payment not completed'];
            }

            return ['success' => false, 'message' => 'Verification failed'];

        } catch (\Exception $e) {
            Log::error('Hubtel verification error', [
                'error' => $e->getMessage(),
                'token' => $token
            ]);

            return ['success' => false, 'message' => 'Verification service unavailable'];
        }
    }

    /**
     * Process payment for loan repayment
     */
    public function processLoanRepayment(Payment $payment, array $gatewayResponse)
    {
        try {
            // Update payment with gateway response
            $payment->update([
                'status' => 'completed',
                'transaction_reference' => $gatewayResponse['reference'] ?? $gatewayResponse['transaction_id'] ?? null,
                'payment_gateway_response' => $gatewayResponse,
                'payment_date' => now()
            ]);

            // Update loan balance
            if ($payment->loan) {
                $payment->loan->updateRemainingBalance();
            }

            Log::info('Loan repayment processed successfully', [
                'payment_id' => $payment->id,
                'loan_id' => $payment->loan_id,
                'amount' => $payment->amount
            ]);

            return ['success' => true, 'message' => 'Payment processed successfully'];

        } catch (\Exception $e) {
            Log::error('Loan repayment processing error', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);

            $payment->update(['status' => 'failed']);
            return ['success' => false, 'message' => 'Payment processing failed'];
        }
    }

    /**
     * Process savings deposit payment
     */
    public function processSavingsDeposit(\App\Models\Saving $saving, array $gatewayResponse)
    {
        try {
            // Update saving with gateway response
            $saving->update([
                'approval_status' => 'approved',
                'status' => 'successful',
                'transaction_reference' => $gatewayResponse['reference'] ?? $gatewayResponse['transaction_id'] ?? null,
            ]);

            // Automatically deduct from outstanding loans (handled by SavingsController)
            // This is called from SavingsController callback, which will handle auto-deduction

            // Update group funds
            $groupFund = \App\Models\GroupFund::getInstance();
            $groupFund->updateTotals();

            Log::info('Savings deposit processed successfully', [
                'saving_id' => $saving->id,
                'user_id' => $saving->user_id,
                'amount' => $saving->amount
            ]);

            return ['success' => true, 'message' => 'Deposit processed successfully'];

        } catch (\Exception $e) {
            Log::error('Savings deposit processing error', [
                'saving_id' => $saving->id,
                'error' => $e->getMessage()
            ]);

            $saving->update(['approval_status' => 'rejected']);
            return ['success' => false, 'message' => 'Deposit processing failed'];
        }
    }

    /**
     * Generate unique payment reference
     */
    public function generateReference(): string
    {
        return 'CU_' . time() . '_' . rand(1000, 9999);
    }

    /**
     * Get supported payment methods
     */
    public function getSupportedMethods(): array
    {
        return [
            'paystack' => [
                'name' => 'Paystack',
                'description' => 'Pay with card, mobile money, or bank transfer',
                'enabled' => !empty($this->paystackSecretKey)
            ],
            'hubtel' => [
                'name' => 'Hubtel',
                'description' => 'Pay with Hubtel wallet or card',
                'enabled' => !empty($this->hubtelApiKey) && !empty($this->hubtelApiSecret)
            ],
            'manual' => [
                'name' => 'Manual Payment',
                'description' => 'Cash or bank transfer (admin approval required)',
                'enabled' => true
            ]
        ];
    }
}