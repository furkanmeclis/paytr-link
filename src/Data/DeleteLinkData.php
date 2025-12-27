<?php

namespace FurkanMeclis\PayTRLink\Data;

use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class DeleteLinkData extends Data
{
    public function __construct(
        #[Required]
        public string $link_id,
    ) {}
}
