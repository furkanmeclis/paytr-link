<?php

namespace FurkanMeclis\PayTRLink\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    public $signature = 'paytr-link:install 
                        {--settings : Publish Spatie Laravel Settings migrations}';

    public $description = 'Installs PayTR Link package and publishes required files';

    public function handle(): int
    {
        $this->info('📦 PayTR Link Package Installation');
        $this->newLine();

        // Config publish
        $this->line('📋 Publishing config file...');
        $this->call('vendor:publish', [
            '--tag' => 'paytr-link-config',
            '--force' => false,
        ]);

        $this->newLine();

        // Settings migration and config publish (optional)
        if ($this->option('settings') || class_exists(\Spatie\LaravelSettings\LaravelSettingsServiceProvider::class)) {
            if (class_exists(\Spatie\LaravelSettings\LaravelSettingsServiceProvider::class)) {
                // Settings config publish
                $this->line('⚙️  Publishing Spatie Laravel Settings config file...');
                try {
                    $this->call('vendor:publish', [
                        '--provider' => 'Spatie\LaravelSettings\LaravelSettingsServiceProvider',
                        '--tag' => 'config',
                        '--force' => false,
                    ]);
                } catch (\Exception $e) {
                    // Config may already be published, continue
                }

                // Settings migration publish
                $this->line('⚙️  Publishing Spatie Laravel Settings migrations...');
                try {
                    $this->call('vendor:publish', [
                        '--provider' => 'Spatie\LaravelSettings\LaravelSettingsServiceProvider',
                        '--tag' => 'migrations',
                        '--force' => false,
                    ]);
                    $this->newLine();
                    $this->info('✅ Settings migrations published!');
                } catch (\Exception $e) {
                    $this->warn('⚠️  Settings migrations could not be published: '.$e->getMessage());
                }

                // Publish PayTR Link settings migrations with dynamic timestamp
                $this->line('⚙️  Publishing PayTR Link settings migrations...');
                try {
                    $this->publishSettingsMigration();
                    $this->newLine();
                    $this->info('✅ PayTR Link settings migrations published!');
                    $this->line('💡 To run migrations: php artisan migrate');
                } catch (\Exception $e) {
                    $this->warn('⚠️  PayTR Link settings migrations could not be published: '.$e->getMessage());
                }
            } else {
                $this->warn('⚠️  Spatie Laravel Settings package is not installed.');
                $this->line('💡 To use Settings: composer require spatie/laravel-settings');
            }
        }

        $this->newLine();
        $this->info('✅ Installation completed!');
        $this->newLine();

        $this->line('📝 Next Steps:');
        $this->line('1. Add your PayTR credentials to your .env file:');
        $this->line('   PAYTR_MERCHANT_ID=your_merchant_id');
        $this->line('   PAYTR_MERCHANT_KEY=your_merchant_key');
        $this->line('   PAYTR_MERCHANT_SALT=your_merchant_salt');
        $this->line('   PAYTR_DEBUG_ON=1');
        $this->newLine();
        $this->line('2. Test the configuration:');
        $this->line('   php artisan paytr-link:test');
        $this->newLine();
        $this->line('3. Create a demo link:');
        $this->line('   php artisan paytr-link:demo');

        if ($this->option('settings') || class_exists(\Spatie\LaravelSettings\LaravelSettingsServiceProvider::class)) {
            $this->newLine();
            $this->line('4. Run migrations (if you will use Settings):');
            $this->line('   php artisan migrate');
            $this->newLine();
            $this->line('5. Set up PayTR Settings:');
            $this->line('   php artisan paytr-link:setup-settings --init');
        }

        return self::SUCCESS;
    }

    /**
     * Publish settings migration with dynamic timestamp
     */
    protected function publishSettingsMigration(): void
    {
        $stubPath = __DIR__.'/../../database/settings/create_paytr_link_settings.php.stub';
        $targetDir = database_path('settings');

        if (! File::exists($stubPath)) {
            throw new \RuntimeException('Migration stub file not found.');
        }

        // Create target directory if it doesn't exist
        if (! File::isDirectory($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        // Generate dynamic timestamp
        $timestamp = date('Y_m_d_His');
        $fileName = "{$timestamp}_create_paytr_link_settings.php";
        $targetPath = $targetDir.'/'.$fileName;

        // Check if migration already exists
        $existingMigrations = File::glob($targetDir.'/*_create_paytr_link_settings.php');
        if (! empty($existingMigrations)) {
            $this->line('   Migration already exists: '.basename($existingMigrations[0]));

            return;
        }

        // Copy stub to target with dynamic name
        $stubContent = File::get($stubPath);
        File::put($targetPath, $stubContent);

        $this->line("   Created: {$fileName}");
    }
}
