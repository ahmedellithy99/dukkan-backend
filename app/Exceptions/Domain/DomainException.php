<?php

namespace App\Exceptions\Domain;

use RuntimeException;

abstract class DomainException extends RuntimeException
{
    public function __construct(
        protected string $apiCode,
        protected int $status = 400,
        string $message = 'Domain error.'
    ) {
        parent::__construct($message);
    }

    public function apiCode(): string
    {
        return $this->apiCode;
    }

    public function status(): int
    {
        return $this->status;
    }
}
