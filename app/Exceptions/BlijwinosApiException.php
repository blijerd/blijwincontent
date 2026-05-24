<?php

namespace App\Exceptions;

use RuntimeException;

class BlijwinosApiException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?int $statusCode = null,
        public readonly ?string $endpoint = null,
    ) {
        parent::__construct($message);
    }
}
