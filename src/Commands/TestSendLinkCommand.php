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

    public $description = 'Rastgele bir ödeme linki oluşturur ve email/SMS ile gönderir';

    public function handle(): int
    {
        $this->info('🧪 PayTR Link Test Gönderimi');
        $this->newLine();

        // Konfigürasyon kontrolü
        $merchantId = config('paytr-link.merchant_id');
        $merchantKey = config('paytr-link.merchant_key');
        $merchantSalt = config('paytr-link.merchant_salt');

        if (empty($merchantId) || empty($merchantKey) || empty($merchantSalt)) {
            $this->error('❌ PayTR konfigürasyonu eksik!');
            $this->line('💡 Önce "php artisan paytr-link:test" komutunu çalıştırın.');

            return self::FAILURE;
        }

        // Gönderim tipini seç
        $sendType = $this->choice(
            'Gönderim tipini seçin',
            ['email', 'sms'],
            0
        );

        $this->newLine();

        // Email veya SMS için gerekli bilgileri al
        if ($sendType === 'email') {
            $email = $this->ask('Email adresini girin');
            
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->error('❌ Geçersiz email adresi!');

                return self::FAILURE;
            }
        } else {
            $phone = $this->ask('Telefon numarasını girin (örn: 5551234567)');
            
            if (empty($phone)) {
                $this->error('❌ Telefon numarası boş olamaz!');

                return self::FAILURE;
            }
        }

        $this->newLine();
        $this->info('🔄 Rastgele ödeme linki oluşturuluyor...');

        try {
            // Rastgele link bilgileri oluştur
            $randomData = $this->generateRandomLinkData();

            $this->line('📝 Oluşturulan link bilgileri:');
            $this->table(
                ['Özellik', 'Değer'],
                [
                    ['İsim', $randomData['name']],
                    ['Fiyat', number_format($randomData['price'], 2).' '.$randomData['currency']->value],
                    ['Tip', $randomData['link_type'] === LinkTypeEnum::Product ? 'Ürün' : 'Toplu Ödeme'],
                    ['Para Birimi', $randomData['currency']->value],
                    ['Max Taksit', (string) $randomData['max_installment']],
                ]
            );
            $this->newLine();

            $this->line('⏳ Link oluşturuluyor...');

            $createLinkData = CreateLinkData::from($randomData);
            $createResponse = PayTRLink::create($createLinkData);

            if (! $createResponse->isSuccess() || ! $createResponse->id) {
                $this->error('❌ Link oluşturulamadı!');
                $this->newLine();

                if ($createResponse->message) {
                    $this->line('Mesaj: '.$createResponse->message);
                }

                if ($createResponse->errors) {
                    $this->line('Hatalar:');
                    foreach ($createResponse->errors as $error) {
                        $this->line('  - '.$error);
                    }
                }

                return self::FAILURE;
            }

            $linkId = $createResponse->id;
            $this->info('✅ Link başarıyla oluşturuldu! (ID: '.$linkId.')');
            $this->newLine();

            // Email veya SMS gönder
            if ($sendType === 'email') {
                $this->line('📧 Email gönderiliyor...');
                
                $sendEmailData = SendEmailData::from([
                    'link_id' => $linkId,
                    'email' => $email,
                ]);

                $sendResponse = PayTRLink::sendEmail($sendEmailData);

                if ($sendResponse->isSuccess()) {
                    $this->info('✅ Email başarıyla gönderildi!');
                    $this->newLine();
                    $this->line('📬 Gönderilen email: '.$email);
                } else {
                    $this->error('❌ Email gönderilemedi!');
                    $this->newLine();

                    if ($sendResponse->message) {
                        $this->line('Mesaj: '.$sendResponse->message);
                    }

                    if ($sendResponse->errors) {
                        $this->line('Hatalar:');
                        foreach ($sendResponse->errors as $error) {
                            $this->line('  - '.$error);
                        }
                    }

                    return self::FAILURE;
                }
            } else {
                $this->line('📱 SMS gönderiliyor...');
                
                $sendSmsData = SendSmsData::from([
                    'link_id' => $linkId,
                    'phone' => $phone,
                ]);

                $sendResponse = PayTRLink::sendSms($sendSmsData);

                if ($sendResponse->isSuccess()) {
                    $this->info('✅ SMS başarıyla gönderildi!');
                    $this->newLine();
                    $this->line('📱 Gönderilen telefon: '.$phone);
                } else {
                    $this->error('❌ SMS gönderilemedi!');
                    $this->newLine();

                    if ($sendResponse->message) {
                        $this->line('Mesaj: '.$sendResponse->message);
                    }

                    if ($sendResponse->errors) {
                        $this->line('Hatalar:');
                        foreach ($sendResponse->errors as $error) {
                            $this->line('  - '.$error);
                        }
                    }

                    return self::FAILURE;
                }
            }

            $this->newLine();

            if ($createResponse->link) {
                $this->info('🔗 Ödeme Linki:');
                $this->line($createResponse->link);
                $this->newLine();
            }

            $this->info('✨ Test başarıyla tamamlandı!');

            return self::SUCCESS;
        } catch (PayTRRequestException $e) {
            $this->error('❌ API İsteği Başarısız!');
            $this->newLine();
            $this->line('Hata: '.$e->getMessage());

            if ($e->response) {
                $this->newLine();
                $this->line('Yanıt Detayları:');
                $this->line(json_encode($e->response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }

            return self::FAILURE;
        } catch (PayTRValidationException $e) {
            $this->error('❌ Validasyon Hatası!');
            $this->newLine();
            $this->line('Hata: '.$e->getMessage());

            if (! empty($e->errors)) {
                $this->newLine();
                $this->line('Hatalar:');
                foreach ($e->errors as $field => $errors) {
                    foreach ((array) $errors as $error) {
                        $this->line('  - '.$field.': '.$error);
                    }
                }
            }

            return self::FAILURE;
        } catch (\Exception $e) {
            $this->error('❌ Beklenmeyen Hata!');
            $this->newLine();
            $this->line('Hata: '.$e->getMessage());
            $this->line('Dosya: '.$e->getFile().':'.$e->getLine());

            return self::FAILURE;
        }
    }

    /**
     * Rastgele link verisi oluştur
     */
    protected function generateRandomLinkData(): array
    {
        $products = [
            'Test Ürünü - Laptop',
            'Test Ürünü - Telefon',
            'Test Ürünü - Tablet',
            'Test Ürünü - Kulaklık',
            'Test Ürünü - Klavye',
            'Test Ürünü - Mouse',
            'Test Ürünü - Monitör',
            'Test Ürünü - Kamera',
        ];

        $linkTypes = [LinkTypeEnum::Product, LinkTypeEnum::Collection];
        $currencies = [CurrencyEnum::TL, CurrencyEnum::USD, CurrencyEnum::EUR];

        $selectedType = $linkTypes[array_rand($linkTypes)];
        $selectedCurrency = $currencies[array_rand($currencies)];
        
        // Rastgele fiyat (10-1000 arası)
        $price = rand(10, 1000) + (rand(0, 99) / 100);
        
        // Rastgele max taksit (1-12 arası)
        $maxInstallment = rand(1, 12);

        $data = [
            'name' => $products[array_rand($products)].' - '.date('d.m.Y H:i'),
            'price' => $price,
            'currency' => $selectedCurrency,
            'link_type' => $selectedType,
            'max_installment' => $maxInstallment,
            'lang' => 'tr',
            'description' => 'Bu bir test linkidir. Rastgele oluşturulmuştur.',
        ];

        // Collection tipi için email ekle
        if ($selectedType === LinkTypeEnum::Collection) {
            $data['email'] = 'test@example.com';
        }

        return $data;
    }
}

