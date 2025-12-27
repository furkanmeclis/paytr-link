<?php

namespace FurkanMeclis\PayTRLink\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    public $signature = 'paytr-link:install 
                        {--settings : Spatie Laravel Settings migration\'larını publish et}';

    public $description = 'PayTR Link paketini kurar ve gerekli dosyaları publish eder';

    public function handle(): int
    {
        $this->info('📦 PayTR Link Paketi Kurulumu');
        $this->newLine();

        // Config publish
        $this->line('📋 Config dosyası publish ediliyor...');
        $this->call('vendor:publish', [
            '--tag' => 'paytr-link-config',
            '--force' => false,
        ]);

        $this->newLine();

        // Settings migration ve config publish (opsiyonel)
        if ($this->option('settings') || class_exists(\Spatie\LaravelSettings\LaravelSettingsServiceProvider::class)) {
            if (class_exists(\Spatie\LaravelSettings\LaravelSettingsServiceProvider::class)) {
                // Settings config publish
                $this->line('⚙️  Spatie Laravel Settings config dosyası publish ediliyor...');
                try {
                    $this->call('vendor:publish', [
                        '--provider' => 'Spatie\LaravelSettings\LaravelSettingsServiceProvider',
                        '--tag' => 'config',
                        '--force' => false,
                    ]);
                } catch (\Exception $e) {
                    // Config zaten publish edilmiş olabilir, devam et
                }

                // Settings migration publish
                $this->line('⚙️  Spatie Laravel Settings migration\'ları publish ediliyor...');
                try {
                    $this->call('vendor:publish', [
                        '--provider' => 'Spatie\LaravelSettings\LaravelSettingsServiceProvider',
                        '--tag' => 'migrations',
                        '--force' => false,
                    ]);
                    $this->newLine();
                    $this->info('✅ Settings migration\'ları publish edildi!');
                    $this->line('💡 Migration\'ları çalıştırmak için: php artisan migrate');
                } catch (\Exception $e) {
                    $this->warn('⚠️  Settings migration publish edilemedi: '.$e->getMessage());
                }
            } else {
                $this->warn('⚠️  Spatie Laravel Settings paketi yüklü değil.');
                $this->line('💡 Settings kullanmak için: composer require spatie/laravel-settings');
            }
        }

        $this->newLine();
        $this->info('✅ Kurulum tamamlandı!');
        $this->newLine();

        $this->line('📝 Sonraki Adımlar:');
        $this->line('1. .env dosyanıza PayTR bilgilerinizi ekleyin:');
        $this->line('   PAYTR_MERCHANT_ID=your_merchant_id');
        $this->line('   PAYTR_MERCHANT_KEY=your_merchant_key');
        $this->line('   PAYTR_MERCHANT_SALT=your_merchant_salt');
        $this->line('   PAYTR_DEBUG_ON=1');
        $this->newLine();
        $this->line('2. Konfigürasyonu test edin:');
        $this->line('   php artisan paytr-link:test');
        $this->newLine();
        $this->line('3. Demo link oluşturun:');
        $this->line('   php artisan paytr-link:demo');

        if ($this->option('settings') || class_exists(\Spatie\LaravelSettings\LaravelSettingsServiceProvider::class)) {
            $this->newLine();
            $this->line('4. Migration\'ları çalıştırın (eğer Settings kullanacaksanız):');
            $this->line('   php artisan migrate');
        }

        return self::SUCCESS;
    }
}
