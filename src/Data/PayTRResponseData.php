<?php

namespace FurkanMeclis\PayTRLink\Data;

use Spatie\LaravelData\Data;

class PayTRResponseData extends Data
{
    public function __construct(
        public string $status,
        public ?string $link = null,
        public ?string $id = null,
        public ?string $message = null,
        public ?array $errors = null,
    ) {}

    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
