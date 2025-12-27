<?php

namespace FurkanMeclis\PayTRLink\Tests;

use FurkanMeclis\PayTRLink\PayTRLinkServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'FurkanMeclis\\PayTRLink\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        $providers = [
            PayTRLinkServiceProvider::class,
        ];

        // Add Spatie Laravel Data service provider if available
        if (class_exists(\Spatie\LaravelData\LaravelDataServiceProvider::class)) {
            $providers[] = \Spatie\LaravelData\LaravelDataServiceProvider::class;
        }

        // Add Spatie Laravel Settings service provider if available
        if (class_exists(\Spatie\LaravelSettings\LaravelSettingsServiceProvider::class)) {
            $providers[] = \Spatie\LaravelSettings\LaravelSettingsServiceProvider::class;
        }

        return $providers;
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        // Set PayTR config for testing
        config()->set('paytr-link.merchant_id', 'test_merchant_id');
        config()->set('paytr-link.merchant_key', 'test_merchant_key');
        config()->set('paytr-link.merchant_salt', 'test_merchant_salt');
        config()->set('paytr-link.debug_on', 1);

        // Set empty settings config to prevent errors (settings package is optional)
        config()->set('settings', array_merge([
            'default_repository' => 'database',
            'repositories' => [
                'database' => [
                    'type' => \Spatie\LaravelSettings\SettingsRepositories\DatabaseSettingsRepository::class,
                    'model' => null,
                    'table' => null,
                    'connection' => null,
                ],
            ],
        ], config('settings', [])));

        /*
         foreach (\Illuminate\Support\Facades\File::allFiles(__DIR__ . '/../database/migrations') as $migration) {
            (include $migration->getRealPath())->up();
         }
         */
    }
}
