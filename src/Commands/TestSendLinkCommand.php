<?php

namespace FurkanMeclis\PayTRLink\Commands;

use FurkanMeclis\PayTRLink\Data\CreateLinkData;
use FurkanMeclis\PayTRLink\Data\SendEmailData;
use FurkanMeclis\PayTRLink\Data\SendSmsData;
use FurkanMeclis\PayTRLink\Enums\CurrencyEnum;
use FurkanMeclis\PayTRLink\Enums\LinkTypeEnum;
use FurkanMeclis\PayTRLink\Exceptions\PayTRRequestException;
use FurkanMeclis\PayTRLink\Exceptions\PayTRValidationException;
use FurkanMeclis\PayTRLink\Facades\PayTRLink;
use Illuminate\Console\Command;

class TestSendLinkCommand extends Command
{
    public $signature = 'paytr-link:test-send';

    public $description = 'Creates a random payment link and sends it via email/SMS';

    public function handle(): int
    {
        $this->info('🧪 PayTR Link Test Sending');
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

        // Select sending type
        $sendType = $this->choice(
            'Select sending type',
            ['email', 'sms'],
            0
        );

        $this->newLine();

        // Get required information for email or SMS
        if ($sendType === 'email') {
            $email = $this->ask('Enter email address');

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->error('❌ Invalid email address!');

                return self::FAILURE;
            }
        } else {
            $phone = $this->ask('Enter phone number (e.g: 05000000000)');

            if (empty($phone)) {
                $this->error('❌ Phone number cannot be empty!');

                return self::FAILURE;
            }

            // Phone number validation: must start with 05 and be 11 digits
            if (! preg_match('/^05\d{9}$/', $phone)) {
                $this->error('❌ Invalid phone number! Must start with 05 and be 11 digits (e.g: 05000000000)');

                return self::FAILURE;
            }
        }

        $this->newLine();
        $this->info('🔄 Creating random payment link...');

        try {
            // Generate random link data
            $randomData = $this->generateRandomLinkData();

            $this->line('📝 Created link information:');
            $this->table(
                ['Property', 'Value'],
                [
                    ['Name', $randomData['name']],
                    ['Price', number_format($randomData['price'], 2).' '.$randomData['currency']->value],
                    ['Type', $randomData['link_type'] === LinkTypeEnum::Product ? 'Product' : 'Bulk Payment'],
                    ['Currency', $randomData['currency']->value],
                    ['Max Installment', (string) $randomData['max_installment']],
                ]
            );
            $this->newLine();

            $this->line('⏳ Creating link...');

            $createLinkData = CreateLinkData::from($randomData);
            $createResponse = PayTRLink::create($createLinkData);

            if (! $createResponse->isSuccess() || ! $createResponse->id) {
                $this->error('❌ Link could not be created!');
                $this->newLine();

                if ($createResponse->message) {
                    $this->line('Message: '.$createResponse->message);
                }

                if ($createResponse->errors) {
                    $this->line('Errors:');
                    foreach ($createResponse->errors as $error) {
                        $this->line('  - '.$error);
                    }
                }

                return self::FAILURE;
            }

            $linkId = $createResponse->id;
            $this->info('✅ Link created successfully! (ID: '.$linkId.')');
            $this->newLine();

            // Send email or SMS
            if ($sendType === 'email') {
                $this->line('📧 Sending email...');

                $sendEmailData = SendEmailData::from([
                    'link_id' => $linkId,
                    'email' => $email,
                ]);

                $sendResponse = PayTRLink::sendEmail($sendEmailData);

                if ($sendResponse->isSuccess()) {
                    $this->info('✅ Email sent successfully!');
                    $this->newLine();
                    $this->line('📬 Sent email: '.$email);
                } else {
                    $this->error('❌ Email could not be sent!');
                    $this->newLine();
                    $this->line('Status: '.$sendResponse->status);
                    $this->newLine();

                    if ($sendResponse->err_msg) {
                        $this->line('Error Message: '.$sendResponse->err_msg);
                        $this->newLine();
                    }

                    if ($sendResponse->message) {
                        $this->line('Message: '.$sendResponse->message);
                        $this->newLine();
                    }

                    if ($sendResponse->reason) {
                        $this->line('Reason: '.$sendResponse->reason);
                        $this->newLine();
                    }

                    if ($sendResponse->errors) {
                        $this->line('Errors:');
                        foreach ($sendResponse->errors as $error) {
                            $this->line('  - '.$error);
                        }
                        $this->newLine();
                    }

                    return self::FAILURE;
                }
            } else {
                $this->line('📱 Sending SMS...');

                $sendSmsData = SendSmsData::from([
                    'link_id' => $linkId,
                    'phone' => $phone,
                ]);

                $sendResponse = PayTRLink::sendSms($sendSmsData);

                if ($sendResponse->isSuccess()) {
                    $this->info('✅ SMS sent successfully!');
                    $this->newLine();
                    $this->line('📱 Sent phone: '.$phone);
                } else {
                    $this->error('❌ SMS could not be sent!');
                    $this->newLine();
                    $this->line('Status: '.$sendResponse->status);
                    $this->newLine();

                    // Show full response
                    $this->line('📋 Full Response:');
                    $this->line(json_encode($sendResponse->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    $this->newLine();

                    if ($sendResponse->err_msg) {
                        $this->line('Error Message: '.$sendResponse->err_msg);
                        $this->newLine();
                    }

                    if ($sendResponse->message) {
                        $this->line('Message: '.$sendResponse->message);
                        $this->newLine();
                    }

                    if ($sendResponse->reason) {
                        $this->line('Reason: '.$sendResponse->reason);
                        $this->newLine();
                    }

                    if ($sendResponse->errors) {
                        $this->line('Errors:');
                        foreach ($sendResponse->errors as $error) {
                            $this->line('  - '.$error);
                        }
                        $this->newLine();
                    }

                    return self::FAILURE;
                }
            }

            $this->newLine();

            if ($createResponse->link) {
                $this->info('🔗 Payment Link:');
                $this->line($createResponse->link);
                $this->newLine();
            }

            $this->info('✨ Test completed successfully!');

            return self::SUCCESS;
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

    /**
     * Generate random link data
     */
    protected function generateRandomLinkData(): array
    {
        $products = [
            'Test Product - Laptop',
            'Test Product - Phone',
            'Test Product - Tablet',
            'Test Product - Headphones',
            'Test Product - Keyboard',
            'Test Product - Mouse',
            'Test Product - Monitor',
            'Test Product - Camera',
        ];

        $linkTypes = [LinkTypeEnum::Product, LinkTypeEnum::Collection];
        $currencies = [CurrencyEnum::TL];

        $selectedType = $linkTypes[array_rand($linkTypes)];
        $selectedCurrency = $currencies[array_rand($currencies)];

        // Random price (between 10-1000)
        $price = rand(10, 1000) + (rand(0, 99) / 100);

        // Random max installment (between 1-12)
        $maxInstallment = rand(1, 12);

        $data = [
            'name' => $products[array_rand($products)].' - '.date('d.m.Y H:i'),
            'price' => $price,
            'currency' => $selectedCurrency,
            'link_type' => $selectedType,
            'max_installment' => $maxInstallment,
            'lang' => 'tr',
            'description' => 'This is a test link. Randomly generated.',
        ];

        // Add email for Collection type
        if ($selectedType === LinkTypeEnum::Collection) {
            $data['email'] = 'test@example.com';
        }

        return $data;
    }
}
