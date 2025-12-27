<?php

namespace FurkanMeclis\PayTRLink\Commands;

use Illuminate\Console\Command;

class TestConfigCommand extends Command
{
    public $signature = 'paytr-link:test';

    public $description = 'PayTR Link konfigürasyonunu test eder';

    public function handle(): int
    {
        $this->info('🔍 PayTR Link Konfigürasyon Testi');
        $this->newLine();

        $merchantId = config('paytr-link.merchant_id');
        $merchantKey = config('paytr-link.merchant_key');
        $merchantSalt = config('paytr-link.merchant_salt');
        $debugOn = config('paytr-link.debug_on', 1);
        $baseUrl = config('paytr-link.api.base_url');
        $timeout = config('paytr-link.timeout', 30);

        // Settings kontrolü
        $usingSettings = false;
        if (class_exists(\Spatie\LaravelSettings\LaravelSettingsServiceProvider::class)) {
            try {
                $settings = app(\FurkanMeclis\PayTRLink\Settings\PayTRSettings::class);
                // Settings'den değerleri al (getMerchantId zaten fallback yapıyor)
                $settingsMerchantId = $settings->getMerchantId();
                $settingsMerchantKey = $settings->getMerchantKey();
                $settingsMerchantSalt = $settings->getMerchantSalt();

                // Eğer Settings'de değer varsa kullan
                if (! empty($settingsMerchantId) || ! empty($settingsMerchantKey) || ! empty($settingsMerchantSalt)) {
                    $usingSettings = true;
                    $merchantId = $settingsMerchantId ?: $merchantId;
                    $merchantKey = $settingsMerchantKey ?: $merchantKey;
                    $merchantSalt = $settingsMerchantSalt ?: $merchantSalt;
                    $debugOn = $settings->getDebugOn();
                }
            } catch (\Exception $e) {
                // Settings yoksa config kullanılacak
            }
        }

        $this->line('📋 Konfigürasyon Bilgileri:');
        $this->table(
            ['Ayar', 'Değer', 'Durum'],
            [
                ['Merchant ID', $this->maskValue($merchantId), $this->checkValue($merchantId)],
                ['Merchant Key', $this->maskValue($merchantKey), $this->checkValue($merchantKey)],
                ['Merchant Salt', $this->maskValue($merchantSalt), $this->checkValue($merchantSalt)],
                ['Debug Mode', $debugOn ? 'Açık' : 'Kapalı', '✓'],
                ['Base URL', $baseUrl, '✓'],
                ['Timeout', $timeout.' saniye', '✓'],
                ['Kaynak', $usingSettings ? 'Settings (DB)' : 'Config', '✓'],
            ]
        );

        $this->newLine();

        // Validasyon kontrolü
        $hasErrors = false;
        $errors = [];

        if (empty($merchantId)) {
            $errors[] = '❌ Merchant ID boş!';
            $hasErrors = true;
        }

        if (empty($merchantKey)) {
            $errors[] = '❌ Merchant Key boş!';
            $hasErrors = true;
        }

        if (empty($merchantSalt)) {
            $errors[] = '❌ Merchant Salt boş!';
            $hasErrors = true;
        }

        if ($hasErrors) {
            $this->error('⚠️  Konfigürasyon Hataları:');
            foreach ($errors as $error) {
                $this->line($error);
            }
            $this->newLine();
            $this->line('💡 Çözüm: .env dosyanıza PayTR bilgilerinizi ekleyin:');
            $this->line('   PAYTR_MERCHANT_ID=your_merchant_id');
            $this->line('   PAYTR_MERCHANT_KEY=your_merchant_key');
            $this->line('   PAYTR_MERCHANT_SALT=your_merchant_salt');

            return self::FAILURE;
        }

        $this->info('✅ Tüm konfigürasyon ayarları doğru!');
        $this->newLine();
        $this->line('🎉 PayTR Link paketi kullanıma hazır!');

        return self::SUCCESS;
    }

    protected function checkValue(?string $value): string
    {
        return ! empty($value) ? '✓' : '✗';
    }

    protected function maskValue(?string $value): string
    {
        if (empty($value)) {
            return '<boş>';
        }

        if (strlen($value) <= 8) {
            return str_repeat('*', strlen($value));
        }

        return substr($value, 0, 4).str_repeat('*', strlen($value) - 8).substr($value, -4);
    }
}
