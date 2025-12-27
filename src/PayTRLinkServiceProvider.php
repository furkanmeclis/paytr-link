<?php

namespace FurkanMeclis\PayTRLink;

use FurkanMeclis\PayTRLink\Commands\PayTRLinkCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PayTRLinkServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('paytr-link')
            ->hasConfigFile()
            ->hasViews()
            ->hasCommand(PayTRLinkCommand::class);
    }

    public function registeringPackage(): void
    {
        // Register PayTRLinkService as singleton
        $this->app->singleton(PayTRLinkService::class, function ($app) {
            return new PayTRLinkService;
        });

        // Register alias
        $this->app->alias(PayTRLinkService::class, 'paytr-link.service');
    }

    public function packageRegistered(): void
    {
        // Auto-discover settings if laravel-settings is installed
        if (class_exists(\Spatie\LaravelSettings\LaravelSettingsServiceProvider::class)) {
            $settingsPath = __DIR__.'/Settings';
            if (is_dir($settingsPath)) {
                $settingsConfig = config('settings', []);
                if (! is_array($settingsConfig)) {
                    $settingsConfig = [];
                }

                // Add auto-discover path if not exists
                $autoDiscover = $settingsConfig['auto_discover_settings'] ?? [];
                if (! in_array($settingsPath, $autoDiscover)) {
                    config([
                        'settings.auto_discover_settings' => array_merge($autoDiscover, [$settingsPath]),
                    ]);
                }
            }
        }
    }

    public function bootingPackage(): void
    {
        // Settings migrations are handled by laravel-settings package itself
        // Users should publish laravel-settings migrations separately
    }
}
