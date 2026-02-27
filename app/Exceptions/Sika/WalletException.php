<?php

namespace App\Exceptions\Sika;

use Exception;

class WalletException extends Exception
{
    public function __construct(string $message = 'Wallet error', int $code = 400, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
