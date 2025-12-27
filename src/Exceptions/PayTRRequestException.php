<?php

namespace FurkanMeclis\PayTRLink\Exceptions;

class PayTRRequestException extends PayTRException
{
    public function __construct(string $message, public readonly ?array $response = null, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
