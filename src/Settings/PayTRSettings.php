<?php

namespace FurkanMeclis\PayTRLink\Settings;

use Spatie\LaravelSettings\Settings;

class PayTRSettings extends Settings
{
    public ?string $merchant_id = null;

    public ?string $merchant_key = null;

    public ?string $merchant_salt = null;

    public ?bool $debug_on = null;

    public static function group(): string
    {
        return 'paytr-link';
    }

    /**
     * Get merchant ID from settings or config fallback
     */
    public function getMerchantId(): string
    {
        return $this->merchant_id ?? config('paytr-link.merchant_id', '');
    }

    /**
     * Get merchant key from settings or config fallback
     */
    public function getMerchantKey(): string
    {
        return $this->merchant_key ?? config('paytr-link.merchant_key', '');
    }

    /**
     * Get merchant salt from settings or config fallback
     */
    public function getMerchantSalt(): string
    {
        return $this->merchant_salt ?? config('paytr-link.merchant_salt', '');
    }

    /**
     * Get debug mode from settings or config fallback
     */
    public function getDebugOn(): int
    {
        $debug = $this->debug_on ?? config('paytr-link.debug_on', 1);

        return $debug ? 1 : 0;
    }
}
