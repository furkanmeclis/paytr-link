<?php

namespace FurkanMeclis\PayTRLink\Data;

use Spatie\LaravelData\Data;

class CallbackData extends Data
{
    public function __construct(
        public string $callback_id,
        public string $merchant_oid,
        public string $status,
        public string $total_amount,
        public string $hash,
        public ?string $failed_reason_code = null,
        public ?string $failed_reason_msg = null,
        public ?string $test_mode = null,
        public ?string $currency = null,
        public ?string $payment_type = null,
        public ?string $payment_amount = null,
        public ?string $installment_count = null,
        public ?string $fraud_status = null,
    ) {}
}
