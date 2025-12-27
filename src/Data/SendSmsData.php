<?php

namespace FurkanMeclis\PayTRLink\Data;

use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class SendSmsData extends Data
{
    public function __construct(
        #[Required]
        public string $link_id,

        #[Required]
        public string $phone,
    ) {}
}
