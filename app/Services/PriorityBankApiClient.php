<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Generic API Client for Priority Bank Central Finance API
 * 
 * This client can be used by any external system to push income/expense data
 * to Priority Bank. It handles authentication, idempotency, and retries.
 */
class PriorityBankApiClient
{
    protected string $baseUrl;
    protected string $apiToken;
    protected int $timeout;
    protected int $maxRetries;

    public function __construct(?string $baseUrl = null, ?string $apiToken = null)
    {
        $this->baseUrl = $baseUrl ?? config('services.priority_bank.api_url', 'http://localhost:8000');
        $this->apiToken = $apiToken ?? config('services.priority_bank.api_token');
        $this->timeout = config('services.priority_bank.timeout', 10);
        $this->maxRetries = config('services.priority_bank.max_retries', 3);
    }

    /**
     * Push income to Priority Bank
     * 
     * @param string $systemId System identifier (e.g., 'gekymedia', 'schoolsgh')
     * @param string $externalTransactionId Unique transaction ID in the external system
     * @param float $amount Income amount
     * @param string $date Date in Y-m-d format
     * @param string $channel Payment channel (bank, momo, cash, other)
     * @param array $options Additional options (notes, income_category_id, income_category_name, account_id, metadata)
     * @return array|null Response data or null on failure
     */
    public function pushIncome(
        string $systemId,
        string $externalTransactionId,
        float $amount,
        string $date,
        string $channel,
        array $options = []
    ): ?array {
        $idempotencyKey = $options['idempotency_key'] ?? $this->generateIdempotencyKey($systemId, $externalTransactionId);

        $payload = array_merge([
            'system_id' => $systemId,
            'external_transaction_id' => $externalTransactionId,
            'amount' => $amount,
            'date' => $date,
            'channel' => $channel,
        ], $options);

        return $this->makeRequest('POST', '/api/central-finance/income', $payload, $idempotencyKey);
    }

    /**
     * Push expense to Priority Bank
     */
    public function pushExpense(
        string $systemId,
        string $externalTransactionId,
        float $amount,
        string $date,
        string $channel,
        array $options = []
    ): ?array {
        $idempotencyKey = $options['idempotency_key'] ?? $this->generateIdempotencyKey($systemId, $externalTransactionId);

        $payload = array_merge([
            'system_id' => $systemId,
            'external_transaction_id' => $externalTransactionId,
            'amount' => $amount,
            'date' => $date,
            'channel' => $channel,
        ], $options);

        return $this->makeRequest('POST', '/api/central-finance/expense', $payload, $idempotencyKey);
    }

    /**
     * Make HTTP request with retry logic
     */
    protected function makeRequest(string $method, string $endpoint, array $payload, ?string $idempotencyKey = null): ?array
    {
        $url = rtrim($this->baseUrl, '/') . $endpoint;
        $attempt = 0;

        while ($attempt < $this->maxRetries) {
            try {
                $headers = [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ];

                if ($this->apiToken) {
                    $headers['Authorization'] = 'Bearer ' . $this->apiToken;
                }

                if ($idempotencyKey) {
                    $headers['X-Idempotency-Key'] = $idempotencyKey;
                }

                $response = Http::timeout($this->timeout)
                    ->withHeaders($headers)
                    ->{strtolower($method)}($url, $payload);

                if ($response->successful()) {
                    $data = $response->json();
                    Log::info('Priority Bank API request successful', [
                        'endpoint' => $endpoint,
                        'system_id' => $payload['system_id'] ?? null,
                        'transaction_id' => $payload['external_transaction_id'] ?? null,
                    ]);
                    return $data;
                }

                // If it's a client error (4xx), don't retry
                if ($response->status() >= 400 && $response->status() < 500) {
                    Log::warning('Priority Bank API client error (no retry)', [
                        'endpoint' => $endpoint,
                        'status' => $response->status(),
                        'response' => $response->body(),
                    ]);
                    return null;
                }

                // Server error (5xx), retry
                $attempt++;
                if ($attempt < $this->maxRetries) {
                    $delay = pow(2, $attempt); // Exponential backoff
                    Log::warning("Priority Bank API server error, retrying in {$delay}s", [
                        'endpoint' => $endpoint,
                        'attempt' => $attempt,
                        'status' => $response->status(),
                    ]);
                    sleep($delay);
                }

            } catch (\Exception $e) {
                $attempt++;
                if ($attempt >= $this->maxRetries) {
                    Log::error('Priority Bank API request failed after retries', [
                        'endpoint' => $endpoint,
                        'error' => $e->getMessage(),
                        'attempts' => $attempt,
                    ]);
                    return null;
                }

                $delay = pow(2, $attempt);
                Log::warning("Priority Bank API exception, retrying in {$delay}s", [
                    'endpoint' => $endpoint,
                    'error' => $e->getMessage(),
                    'attempt' => $attempt,
                ]);
                sleep($delay);
            }
        }

        return null;
    }

    /**
     * Generate idempotency key
     */
    protected function generateIdempotencyKey(string $systemId, string $externalTransactionId): string
    {
        return hash('sha256', "{$systemId}:{$externalTransactionId}");
    }
}

