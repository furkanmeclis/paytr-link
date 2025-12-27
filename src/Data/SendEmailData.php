<?php

namespace FurkanMeclis\PayTRLink\Data;

use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class SendEmailData extends Data
{
    public function __construct(
        #[Required]
        public string $link_id,

        #[Required]
        #[Email]
        public string $email,
    ) {}
}
