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
        return [
            PayTRLinkServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        // Set PayTR config for testing
        config()->set('paytr-link.merchant_id', 'test_merchant_id');
        config()->set('paytr-link.merchant_key', 'test_merchant_key');
        config()->set('paytr-link.merchant_salt', 'test_merchant_salt');
        config()->set('paytr-link.debug_on', 1);

        /*
         foreach (\Illuminate\Support\Facades\File::allFiles(__DIR__ . '/../database/migrations') as $migration) {
            (include $migration->getRealPath())->up();
         }
         */
    }
}
