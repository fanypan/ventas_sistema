<?php

namespace App\Exceptions;

use App\Http\Responses\JsonEnvelope;
use RuntimeException;

class BusinessRuleException extends RuntimeException
{
    public function __construct(
        string $message,
        private readonly int $status = 422,
    ) {
        parent::__construct($message, $status);
    }

    public function status(): int
    {
        return $this->status;
    }

    public function payload(): array
    {
        return JsonEnvelope::payload('error', $this->getMessage());
    }
}
