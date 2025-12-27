<?php

namespace FurkanMeclis\PayTRLink\Exceptions;

class PayTRValidationException extends PayTRException
{
    public function __construct(string $message, public readonly array $errors = [], int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
