<?php

namespace FurkanMeclis\PayTRLink\Data;

use FurkanMeclis\PayTRLink\Enums\CurrencyEnum;
use FurkanMeclis\PayTRLink\Enums\LinkTypeEnum;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class CreateLinkData extends Data
{
    public function __construct(
        #[Required]
        public string $name,

        #[Required]
        #[Min(1)]
        public int|float $price,

        public CurrencyEnum $currency = CurrencyEnum::TL,

        #[Required]
        public LinkTypeEnum $link_type = LinkTypeEnum::Product,

        #[Min(1)]
        #[Max(12)]
        public int $max_installment = 12,

        #[Min(1)]
        public ?int $min_count = null,

        #[Email]
        public ?string $email = null,

        public string $lang = 'tr',

        public ?string $expiry_date = null,

        public ?string $description = null,
    ) {
        // Set default min_count for product type
        if ($this->link_type === LinkTypeEnum::Product && $this->min_count === null) {
            $this->min_count = 1;
        }
    }
}
