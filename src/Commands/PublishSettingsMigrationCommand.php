<?php

namespace FurkanMeclis\PayTRLink\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishSettingsMigrationCommand extends Command
{
    public $signature = 'paytr-link:publish-settings-migration';

    public $description = 'Publishes PayTR Link settings migration with dynamic timestamp';

    public function handle(): int
    {
        $stubPath = __DIR__.'/../../database/settings/create_paytr_link_settings.php.stub';
        $targetDir = database_path('settings');

        if (! File::exists($stubPath)) {
            $this->error('❌ Migration stub file not found.');

            return self::FAILURE;
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
            $this->info('✅ PayTR Link settings migration already exists.');
            $this->line('   Existing migration: '.basename($existingMigrations[0]));

            return self::SUCCESS;
        }

        // Copy stub to target with dynamic name
        $stubContent = File::get($stubPath);
        File::put($targetPath, $stubContent);

        $this->info('✅ PayTR Link settings migration published successfully!');
        $this->line("   Migration file: {$fileName}");

        return self::SUCCESS;
    }
}
