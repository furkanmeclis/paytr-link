<?php

namespace FurkanMeclis\PayTRLink\Commands;

use FurkanMeclis\PayTRLink\Data\CreateLinkData;
use FurkanMeclis\PayTRLink\Enums\CurrencyEnum;
use FurkanMeclis\PayTRLink\Enums\LinkTypeEnum;
use FurkanMeclis\PayTRLink\Exceptions\PayTRRequestException;
use FurkanMeclis\PayTRLink\Exceptions\PayTRValidationException;
use FurkanMeclis\PayTRLink\Facades\PayTRLink;
use Illuminate\Console\Command;

class DemoLinkCommand extends Command
{
    public $signature = 'paytr-link:demo 
                        {--type=product : Link tipi (product veya collection)}
                        {--price=100 : Fiyat (TL)}';

    public $description = 'Creates a PayTR Link demo link and shows the result';

    public function handle(): int
    {
        $this->info('🚀 Creating PayTR Link Demo Link...');
        $this->newLine();

        // Configuration check
        $merchantId = config('paytr-link.merchant_id');
        $merchantKey = config('paytr-link.merchant_key');
        $merchantSalt = config('paytr-link.merchant_salt');

        if (empty($merchantId) || empty($merchantKey) || empty($merchantSalt)) {
            $this->error('❌ PayTR configuration is missing!');
            $this->line('💡 First run "php artisan paytr-link:test" command.');

            return self::FAILURE;
        }

        // Get parameters
        $linkType = $this->option('type');
        $price = (float) $this->option('price');

        if (! in_array($linkType, ['product', 'collection'])) {
            $this->error('❌ Invalid link type! Only "product" or "collection" can be used.');

            return self::FAILURE;
        }

        if ($price <= 0) {
            $this->error('❌ Price must be greater than 0!');

            return self::FAILURE;
        }

        try {
            $this->line('📝 Link information:');
            $this->table(
                ['Property', 'Value'],
                [
                    ['Type', $linkType === 'product' ? 'Product' : 'Bulk Payment'],
                    ['Price', number_format($price, 2).' TL'],
                    ['Currency', 'TL'],
                    ['Max Installment', '12'],
                ]
            );
            $this->newLine();

            $this->line('⏳ Sending request to API...');

            $data = CreateLinkData::from([
                'name' => 'Demo Product - '.date('d.m.Y H:i'),
                'price' => $price,
                'currency' => CurrencyEnum::TL,
                'link_type' => $linkType === 'product' ? LinkTypeEnum::Product : LinkTypeEnum::Collection,
                'max_installment' => 12,
                'lang' => 'tr',
                'description' => 'This is a demo link.',
            ]);

            $response = PayTRLink::create($data);

            if ($response->isSuccess()) {
                $this->newLine();
                $this->info('✅ Link created successfully!');
                $this->newLine();

                $this->line('📋 Link Details:');
                $this->table(
                    ['Field', 'Value'],
                    [
                        ['Link ID', $response->id ?? 'N/A'],
                        ['Status', $response->status],
                        ['Message', $response->message ?? 'Success'],
                    ]
                );

                $this->newLine();

                if ($response->link) {
                    $this->info('🔗 Payment Link:');
                    $this->line($response->link);
                    $this->newLine();

                    // Suggestion for copying link
                    if (PHP_OS_FAMILY === 'Darwin') {
                        $this->comment('💡 Tip: To copy link: echo "'.$response->link.'" | pbcopy');
                    } elseif (PHP_OS_FAMILY === 'Linux') {
                        $this->comment('💡 Tip: To copy link: echo "'.$response->link.'" | xclip -selection clipboard');
                    }

                    $this->newLine();
                }

                $this->line('✨ Demo link created successfully!');

                return self::SUCCESS;
            } else {
                $this->error('❌ Link could not be created!');
                $this->newLine();

                if ($response->message) {
                    $this->line('Message: '.$response->message);
                }

                if ($response->errors) {
                    $this->line('Errors:');
                    foreach ($response->errors as $error) {
                        $this->line('  - '.$error);
                    }
                }

                return self::FAILURE;
            }
        } catch (PayTRRequestException $e) {
            $this->error('❌ API Request Failed!');
            $this->newLine();
            $this->line('Error: '.$e->getMessage());

            if ($e->response) {
                $this->newLine();
                $this->line('Response Details:');
                $this->line(json_encode($e->response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }

            return self::FAILURE;
        } catch (PayTRValidationException $e) {
            $this->error('❌ Validation Error!');
            $this->newLine();
            $this->line('Error: '.$e->getMessage());

            if (! empty($e->errors)) {
                $this->newLine();
                $this->line('Errors:');
                foreach ($e->errors as $field => $errors) {
                    foreach ((array) $errors as $error) {
                        $this->line('  - '.$field.': '.$error);
                    }
                }
            }

            return self::FAILURE;
        } catch (\Exception $e) {
            $this->error('❌ Unexpected Error!');
            $this->newLine();
            $this->line('Error: '.$e->getMessage());
            $this->line('File: '.$e->getFile().':'.$e->getLine());

            return self::FAILURE;
        }
    }
}
