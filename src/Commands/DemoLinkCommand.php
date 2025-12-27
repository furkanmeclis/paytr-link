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

    public $description = 'PayTR Link demo linki oluşturur ve sonucu gösterir';

    public function handle(): int
    {
        $this->info('🚀 PayTR Link Demo Link Oluşturuluyor...');
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

        // Parametreleri al
        $linkType = $this->option('type');
        $price = (float) $this->option('price');

        if (! in_array($linkType, ['product', 'collection'])) {
            $this->error('❌ Geçersiz link tipi! Sadece "product" veya "collection" kullanılabilir.');

            return self::FAILURE;
        }

        if ($price <= 0) {
            $this->error('❌ Fiyat 0\'dan büyük olmalıdır!');

            return self::FAILURE;
        }

        try {
            $this->line('📝 Link bilgileri:');
            $this->table(
                ['Özellik', 'Değer'],
                [
                    ['Tip', $linkType === 'product' ? 'Ürün' : 'Toplu Ödeme'],
                    ['Fiyat', number_format($price, 2).' TL'],
                    ['Para Birimi', 'TL'],
                    ['Max Taksit', '12'],
                ]
            );
            $this->newLine();

            $this->line('⏳ API\'ye istek gönderiliyor...');

            $data = CreateLinkData::from([
                'name' => 'Demo Ürün - '.date('d.m.Y H:i'),
                'price' => $price,
                'currency' => CurrencyEnum::TL,
                'link_type' => $linkType === 'product' ? LinkTypeEnum::Product : LinkTypeEnum::Collection,
                'max_installment' => 12,
                'lang' => 'tr',
                'description' => 'Bu bir demo linkidir.',
            ]);

            $response = PayTRLink::create($data);

            if ($response->isSuccess()) {
                $this->newLine();
                $this->info('✅ Link başarıyla oluşturuldu!');
                $this->newLine();

                $this->line('📋 Link Detayları:');
                $this->table(
                    ['Alan', 'Değer'],
                    [
                        ['Link ID', $response->id ?? 'N/A'],
                        ['Durum', $response->status],
                        ['Mesaj', $response->message ?? 'Başarılı'],
                    ]
                );

                $this->newLine();

                if ($response->link) {
                    $this->info('🔗 Ödeme Linki:');
                    $this->line($response->link);
                    $this->newLine();

                    // Link'i kopyalamak için öneri
                    if (PHP_OS_FAMILY === 'Darwin') {
                        $this->comment('💡 İpucu: Linki kopyalamak için: echo "'.$response->link.'" | pbcopy');
                    } elseif (PHP_OS_FAMILY === 'Linux') {
                        $this->comment('💡 İpucu: Linki kopyalamak için: echo "'.$response->link.'" | xclip -selection clipboard');
                    }

                    $this->newLine();
                }

                $this->line('✨ Demo link başarıyla oluşturuldu!');

                return self::SUCCESS;
            } else {
                $this->error('❌ Link oluşturulamadı!');
                $this->newLine();

                if ($response->message) {
                    $this->line('Mesaj: '.$response->message);
                }

                if ($response->errors) {
                    $this->line('Hatalar:');
                    foreach ($response->errors as $error) {
                        $this->line('  - '.$error);
                    }
                }

                return self::FAILURE;
            }
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
}
