<?php

namespace App\Exceptions\Sika;

class InsufficientFundsException extends WalletException
{
    public float $availableBalance;
    public float $requestedAmount;

    public function __construct(
        string $message = 'Insufficient funds',
        int $code = 402,
        float $availableBalance = 0,
        float $requestedAmount = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->availableBalance = $availableBalance;
        $this->requestedAmount = $requestedAmount;
    }

    public function getAvailableBalance(): float
    {
        return $this->availableBalance;
    }

    public function getRequestedAmount(): float
    {
        return $this->requestedAmount;
    }
}
