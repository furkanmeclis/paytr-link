<?php

namespace FurkanMeclis\PayTRLink\Commands;

use Illuminate\Console\Command;

class TestConfigCommand extends Command
{
    public $signature = 'paytr-link:test';

    public $description = 'Tests PayTR Link configuration';

    public function handle(): int
    {
        $this->info('🔍 PayTR Link Configuration Test');
        $this->newLine();

        $merchantId = config('paytr-link.merchant_id');
        $merchantKey = config('paytr-link.merchant_key');
        $merchantSalt = config('paytr-link.merchant_salt');
        $debugOn = config('paytr-link.debug_on', 1);
        $baseUrl = config('paytr-link.api.base_url');
        $timeout = config('paytr-link.timeout', 30);

        // Settings check
        $usingSettings = false;
        if (class_exists(\Spatie\LaravelSettings\LaravelSettingsServiceProvider::class)) {
            try {
                $settings = app(\FurkanMeclis\PayTRLink\Settings\PayTRSettings::class);
                // Get values from Settings (getMerchantId already does fallback)
                $settingsMerchantId = $settings->getMerchantId();
                $settingsMerchantKey = $settings->getMerchantKey();
                $settingsMerchantSalt = $settings->getMerchantSalt();

                // Use if value exists in Settings
                if (! empty($settingsMerchantId) || ! empty($settingsMerchantKey) || ! empty($settingsMerchantSalt)) {
                    $usingSettings = true;
                    $merchantId = $settingsMerchantId ?: $merchantId;
                    $merchantKey = $settingsMerchantKey ?: $merchantKey;
                    $merchantSalt = $settingsMerchantSalt ?: $merchantSalt;
                    $debugOn = $settings->getDebugOn();
                }
            } catch (\Exception $e) {
                // If Settings doesn't exist, config will be used
            }
        }

        $this->line('📋 Configuration Information:');
        $this->table(
            ['Setting', 'Value', 'Status'],
            [
                ['Merchant ID', $this->maskValue($merchantId), $this->checkValue($merchantId)],
                ['Merchant Key', $this->maskValue($merchantKey), $this->checkValue($merchantKey)],
                ['Merchant Salt', $this->maskValue($merchantSalt), $this->checkValue($merchantSalt)],
                ['Debug Mode', $debugOn ? 'On' : 'Off', '✓'],
                ['Base URL', $baseUrl, '✓'],
                ['Timeout', $timeout.' seconds', '✓'],
                ['Source', $usingSettings ? 'Settings (DB)' : 'Config', '✓'],
            ]
        );

        $this->newLine();

        // Validation check
        $hasErrors = false;
        $errors = [];

        if (empty($merchantId)) {
            $errors[] = '❌ Merchant ID is empty!';
            $hasErrors = true;
        }

        if (empty($merchantKey)) {
            $errors[] = '❌ Merchant Key is empty!';
            $hasErrors = true;
        }

        if (empty($merchantSalt)) {
            $errors[] = '❌ Merchant Salt is empty!';
            $hasErrors = true;
        }

        if ($hasErrors) {
            $this->error('⚠️  Configuration Errors:');
            foreach ($errors as $error) {
                $this->line($error);
            }
            $this->newLine();
            $this->line('💡 Solution: Add your PayTR credentials to your .env file:');
            $this->line('   PAYTR_MERCHANT_ID=your_merchant_id');
            $this->line('   PAYTR_MERCHANT_KEY=your_merchant_key');
            $this->line('   PAYTR_MERCHANT_SALT=your_merchant_salt');

            return self::FAILURE;
        }

        $this->info('✅ All configuration settings are correct!');
        $this->newLine();
        $this->line('🎉 PayTR Link package is ready to use!');

        return self::SUCCESS;
    }

    protected function checkValue(?string $value): string
    {
        return ! empty($value) ? '✓' : '✗';
    }

    protected function maskValue(?string $value): string
    {
        if (empty($value)) {
            return '<empty>';
        }

        if (strlen($value) <= 8) {
            return str_repeat('*', strlen($value));
        }

        return substr($value, 0, 4).str_repeat('*', strlen($value) - 8).substr($value, -4);
    }
}
