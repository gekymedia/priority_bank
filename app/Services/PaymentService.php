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
        $this->hubtelApiKey = config('services.hubtel.api_key');
        $this->hubtelApiSecret = config('services.hubtel.api_secret');
    }

    /**
     * Initialize Paystack payment
     */
    public function initializePaystackPayment(array $data)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->paystackSecretKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.paystack.co/transaction/initialize', [
                'email' => $data['email'],
                'amount' => $data['amount'] * 100, // Paystack expects amount in kobo
                'currency' => 'GHS',
                'reference' => $data['reference'],
                'callback_url' => $data['callback_url'],
                'metadata' => [
                    'loan_id' => $data['loan_id'] ?? null,
                    'user_id' => $data['user_id'],
                    'payment_type' => 'loan_repayment'
                ]
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Paystack initialization failed', [
                'response' => $response->body(),
                'status' => $response->status()
            ]);

            return ['error' => 'Payment initialization failed'];

        } catch (\Exception $e) {
            Log::error('Paystack payment initialization error', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return ['error' => 'Payment service temporarily unavailable'];
        }
    }

    /**
     * Verify Paystack payment
     */
    public function verifyPaystackPayment(string $reference)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->paystackSecretKey,
            ])->get("https://api.paystack.co/transaction/verify/{$reference}");

            if ($response->successful()) {
                $data = $response->json();

                if ($data['data']['status'] === 'success') {
                    return [
                        'success' => true,
                        'data' => $data['data'],
                        'amount' => $data['data']['amount'] / 100, // Convert from kobo to cedis
                        'reference' => $data['data']['reference'],
                        'metadata' => $data['data']['metadata'] ?? []
                    ];
                }

                return ['success' => false, 'message' => 'Payment not successful'];
            }

            return ['success' => false, 'message' => 'Verification failed'];

        } catch (\Exception $e) {
            Log::error('Paystack verification error', [
                'error' => $e->getMessage(),
                'reference' => $reference
            ]);

            return ['success' => false, 'message' => 'Verification service unavailable'];
        }
    }

    /**
     * Initialize Hubtel payment
     */
    public function initializeHubtelPayment(array $data)
    {
        try {
            $auth = base64_encode($this->hubtelApiKey . ':' . $this->hubtelApiSecret);

            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $auth,
                'Content-Type' => 'application/json',
            ])->post('https://api.hubtel.com/v2/pos/onlinecheckout/items/initiate', [
                'invoice' => [
                    'items' => [
                        [
                            'name' => 'Loan Repayment',
                            'quantity' => 1,
                            'unitPrice' => $data['amount'],
                            'totalPrice' => $data['amount'],
                            'description' => 'Loan repayment for ' . ($data['loan_id'] ? 'Loan #' . $data['loan_id'] : 'Credit Union')
                        ]
                    ],
                    'totalAmount' => $data['amount'],
                    'description' => 'Credit Union Loan Repayment',
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