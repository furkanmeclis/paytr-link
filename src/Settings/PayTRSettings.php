<?php

namespace FurkanMeclis\PayTRLink\Settings;

use Spatie\LaravelSettings\Settings;

class PayTRSettings extends Settings
{
    public string $merchant_id = '';

    public string $merchant_key = '';

    public string $merchant_salt = '';

    public bool $debug_on = false;

    public static function group(): string
    {
        return 'paytr-link';
    }

    /**
     * Get merchant ID from settings or config fallback
     */
    public function getMerchantId(): string
    {
        if (empty($this->merchant_id)) {
            return config('paytr-link.merchant_id', '');
        }

        return $this->merchant_id;
    }

    /**
     * Get merchant key from settings or config fallback
     */
    public function getMerchantKey(): string
    {
        if (empty($this->merchant_key)) {
            return config('paytr-link.merchant_key', '');
        }

        return $this->merchant_key;
    }

    /**
     * Get merchant salt from settings or config fallback
     */
    public function getMerchantSalt(): string
    {
        if (empty($this->merchant_salt)) {
            return config('paytr-link.merchant_salt', '');
        }

        return $this->merchant_salt;
    }

    /**
     * Get debug mode from settings or config fallback
     */
    public function getDebugOn(): int
    {
        // If debug_on is false and merchant_id is empty, it means settings haven't been initialized
        // In this case, use config fallback
        if ($this->debug_on === false && empty($this->merchant_id)) {
            $debug = config('paytr-link.debug_on', 1);

            return $debug ? 1 : 0;
        }

        return $this->debug_on ? 1 : 0;
    }
}
