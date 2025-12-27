<?php

namespace FurkanMeclis\PayTRLink\Commands;

use FurkanMeclis\PayTRLink\Settings\PayTRSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Spatie\LaravelSettings\SettingsRepositories\SettingsRepository;

class SetupSettingsCommand extends Command
{
    public $signature = 'paytr-link:setup-settings 
                        {--init : Initialize settings with default values from config}';

    public $description = 'Sets up PayTR Settings by creating and running the settings migration';

    public function handle(): int
    {
        $this->info('⚙️  PayTR Settings Setup');
        $this->newLine();

        // Check if Spatie Laravel Settings is installed
        if (! class_exists(\Spatie\LaravelSettings\LaravelSettingsServiceProvider::class)) {
            $this->error('❌ Spatie Laravel Settings package is not installed.');
            $this->line('💡 Install it with: composer require spatie/laravel-settings');

            return self::FAILURE;
        }

        // Check if settings table exists
        if (! Schema::hasTable('settings')) {
            $this->error('❌ Settings table does not exist.');
            $this->line('💡 First publish and run Spatie Laravel Settings migrations:');
            $this->line('   php artisan vendor:publish --provider="Spatie\LaravelSettings\LaravelSettingsServiceProvider" --tag="migrations"');
            $this->line('   php artisan migrate');

            return self::FAILURE;
        }

        // Get settings repository
        try {
<<<<<<< Updated upstream
<<<<<<< Updated upstream
            $repository = $this->getSettingsRepository();
        } catch (\Exception $e) {
            $this->error('❌ Could not resolve SettingsRepository.');
            $this->line('💡 Make sure Spatie Laravel Settings is properly configured.');
            $this->line('Error: '.$e->getMessage());
=======
=======
>>>>>>> Stashed changes
            $repository = app(SettingsRepository::class);
        } catch (\Exception $e) {
            $this->error('❌ Could not resolve SettingsRepository.');
            $this->line('💡 Make sure Spatie Laravel Settings is properly configured.');
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes

            return self::FAILURE;
        }

        // Check if PayTRSettings migration already exists
        $group = PayTRSettings::group();
        $properties = ['merchant_id', 'merchant_key', 'merchant_salt', 'debug_on'];

        $allExist = true;
        foreach ($properties as $property) {
            if (! $repository->checkIfPropertyExists($group, $property)) {
                $allExist = false;
                break;
            }
        }

        if ($allExist) {
            $this->info('✅ PayTR Settings are already set up in the database.');
            $this->newLine();

            if ($this->option('init')) {
                $this->line('🔄 Initializing settings with config values...');
                $this->initializeSettings();

                return self::SUCCESS;
            }

            $this->line('💡 Use --init flag to initialize settings with config values.');

            return self::SUCCESS;
        }

        // Create settings migration
        $this->line('📋 Creating PayTR Settings migration...');

        try {
            // Create settings using repository
            $this->createSettingsUsingRepository($repository, $group, $properties);

            $this->newLine();
            $this->info('✅ Settings migration completed!');
            $this->newLine();

            // Initialize settings if requested
            if ($this->option('init')) {
                $this->line('🔄 Initializing settings with config values...');
                $this->initializeSettings();
            } else {
                $this->line('💡 Use --init flag to initialize settings with config values.');
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Error setting up settings: '.$e->getMessage());
            $this->newLine();
            $this->line('Stack trace:');
            $this->line($e->getTraceAsString());

            return self::FAILURE;
        }
    }

    /**
<<<<<<< Updated upstream
<<<<<<< Updated upstream
     * Get SettingsRepository instance
     */
    protected function getSettingsRepository(): SettingsRepository
    {
        // Get repository name from PayTRSettings if specified
        $repositoryName = PayTRSettings::repository();

        // If no specific repository, use default
        if ($repositoryName === null) {
            $repositoryName = config('settings.default_repository', 'database');
        }

        // Get repository configuration
        $repositories = config('settings.repositories', []);
        if (! isset($repositories[$repositoryName])) {
            throw new \RuntimeException("Settings repository '{$repositoryName}' not found in config.");
        }

        $repositoryConfig = $repositories[$repositoryName];
        $repositoryClass = $repositoryConfig['type'] ?? \Spatie\LaravelSettings\SettingsRepositories\DatabaseSettingsRepository::class;

        // Resolve repository from container or create new instance
        if (app()->bound($repositoryClass)) {
            return app($repositoryClass);
        }

        // Create repository instance with config
        return new $repositoryClass($repositoryConfig);
    }

    /**
     * Create settings using SettingsRepository
     */
=======
     * Create settings using SettingsRepository
     */
>>>>>>> Stashed changes
=======
     * Create settings using SettingsRepository
     */
>>>>>>> Stashed changes
    protected function createSettingsUsingRepository(SettingsRepository $repository, string $group, array $properties): void
    {
        foreach ($properties as $property) {
            if (! $repository->checkIfPropertyExists($group, $property)) {
                $repository->createProperty($group, $property, null);
                $this->line("  ✓ Created setting: {$group}.{$property}");
            }
        }
    }

    /**
     * Initialize settings with values from config
     */
    protected function initializeSettings(): void
    {
        try {
            $settings = app(PayTRSettings::class);

            $merchantId = config('paytr-link.merchant_id');
            $merchantKey = config('paytr-link.merchant_key');
            $merchantSalt = config('paytr-link.merchant_salt');
            $debugOn = config('paytr-link.debug_on', 1);

            if (! empty($merchantId)) {
                $settings->merchant_id = $merchantId;
                $this->line('  ✓ Set merchant_id from config');
            }

            if (! empty($merchantKey)) {
                $settings->merchant_key = $merchantKey;
                $this->line('  ✓ Set merchant_key from config');
            }

            if (! empty($merchantSalt)) {
                $settings->merchant_salt = $merchantSalt;
                $this->line('  ✓ Set merchant_salt from config');
            }

            $settings->debug_on = (bool) $debugOn;
            $this->line('  ✓ Set debug_on from config');

            $settings->save();

            $this->newLine();
            $this->info('✅ Settings initialized successfully!');
        } catch (\Exception $e) {
            $this->error('❌ Error initializing settings: '.$e->getMessage());
            $this->newLine();
            $this->line('💡 You can manually set settings using:');
            $this->line('   $settings = app(\FurkanMeclis\PayTRLink\Settings\PayTRSettings::class);');
            $this->line('   $settings->merchant_id = "your_value";');
            $this->line('   $settings->save();');
        }
    }
}
