# Changelog

All notable changes to `furkanmeclis/paytr-link` will be documented in this file.

## V0.2 - 2025-12-27

### PayTR Link API Laravel Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/furkanmeclis/paytr-link.svg?style=flat-square)](https://packagist.org/packages/furkanmeclis/paytr-link)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/furkanmeclis/paytr-link/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/furkanmeclis/paytr-link/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/furkanmeclis/paytr-link.svg?style=flat-square)](https://packagist.org/packages/furkanmeclis/paytr-link)

PayTR Link API entegrasyonu için Laravel paketi. Bu paket, PayTR Link API ile kolayca entegre olmanızı sağlar.

#### Özellikler

- ✅ PayTR Link API tüm endpoint'leri
- ✅ Type-safe Data Transfer Objects (Spatie Laravel Data)
- ✅ Settings yönetimi (Spatie Laravel Settings)
- ✅ Kolay kullanım için Facade desteği
- ✅ Kapsamlı test coverage
- ✅ PHP 8.1+ desteği

#### Kurulum

Paketi Composer ile yükleyin:

```bash
composer require furkanmeclis/paytr-link

```
Config dosyasını yayınlayın:

```bash
php artisan vendor:publish --tag="paytr-link-config"

```
**Not**: Paket çalışması için migration gerekli değildir. Eğer link'leri veritabanında saklamak isterseniz, migration stub dosyasını kullanabilirsiniz.

Eğer Spatie Laravel Settings kullanacaksanız:

```bash
php artisan vendor:publish --provider="Spatie\LaravelSettings\LaravelSettingsServiceProvider" --tag="migrations"
php artisan migrate

```
#### Yapılandırma

`.env` dosyanıza PayTR bilgilerinizi ekleyin:

```env
PAYTR_MERCHANT_ID=your_merchant_id
PAYTR_MERCHANT_KEY=your_merchant_key
PAYTR_MERCHANT_SALT=your_merchant_salt
PAYTR_DEBUG_ON=1

```
Config dosyası (`config/paytr-link.php`) ile de yapılandırabilirsiniz.

#### Kullanım

##### Link Oluşturma

```php
use FurkanMeclis\PayTRLink\Facades\PayTRLink;
use FurkanMeclis\PayTRLink\Data\CreateLinkData;
use FurkanMeclis\PayTRLink\Enums\CurrencyEnum;
use FurkanMeclis\PayTRLink\Enums\LinkTypeEnum;

$data = CreateLinkData::from([
    'name' => 'Web Tasarım Hizmeti',
    'price' => 1500.00, // TL cinsinden
    'currency' => CurrencyEnum::TL,
    'link_type' => LinkTypeEnum::Product,
    'max_installment' => 12,
    'lang' => 'tr',
    'expiry_date' => '2025-12-31 23:59:59',
]);

$response = PayTRLink::create($data);

if ($response->isSuccess()) {
    $link = $response->link;
    $linkId = $response->id;
}

```
##### Collection Link Oluşturma

```php
$data = CreateLinkData::from([
    'name' => 'Toplu Ödeme',
    'price' => 5000.00,
    'currency' => CurrencyEnum::TL,
    'link_type' => LinkTypeEnum::Collection,
    'email' => 'customer@example.com', // Collection için zorunlu
    'max_installment' => 12,
]);

$response = PayTRLink::create($data);

```
##### Link Silme

```php
use FurkanMeclis\PayTRLink\Data\DeleteLinkData;

$response = PayTRLink::delete(DeleteLinkData::from([
    'link_id' => 'link_id_here',
]));

// Veya direkt string ile
$response = PayTRLink::delete('link_id_here');

```
##### SMS Gönderme

```php
use FurkanMeclis\PayTRLink\Data\SendSmsData;

$response = PayTRLink::sendSms(SendSmsData::from([
    'link_id' => 'link_id_here',
    'phone' => '5551234567',
]));

```
##### Email Gönderme

```php
use FurkanMeclis\PayTRLink\Data\SendEmailData;

$response = PayTRLink::sendEmail(SendEmailData::from([
    'link_id' => 'link_id_here',
    'email' => 'customer@example.com',
]));

```
##### Callback Doğrulama

```php
use Illuminate\Http\Request;

public function handleCallback(Request $request)
{
    if (PayTRLink::validateCallback($request->all())) {
        // İşlem başarılı
        $callbackData = \FurkanMeclis\PayTRLink\Data\CallbackData::from($request->all());
        
        if ($callbackData->status === 'success') {
            // Ödeme başarılı, işlemi güncelle
            echo "OK";
        } else {
            // Ödeme başarısız
            echo "OK";
        }
    } else {
        return response('Invalid hash', 400);
    }
}

```
##### Service Injection

Facade yerine dependency injection da kullanabilirsiniz:

```php
use FurkanMeclis\PayTRLink\PayTRLinkService;

class PaymentController
{
    public function __construct(
        protected PayTRLinkService $paytrLink
    ) {}

    public function createLink(CreateLinkData $data)
    {
        $response = $this->paytrLink->create($data);
        
        return response()->json($response);
    }
}

```
#### Spatie Laravel Settings Entegrasyonu

Paket, Spatie Laravel Settings ile entegre çalışır. Settings kullanarak ayarları veritabanında saklayabilirsiniz:

```php
use FurkanMeclis\PayTRLink\Settings\PayTRSettings;

$settings = app(PayTRSettings::class);
$settings->merchant_id = 'your_merchant_id';
$settings->merchant_key = 'your_merchant_key';
$settings->merchant_salt = 'your_merchant_salt';
$settings->debug_on = true;
$settings->save();

```
Settings kullanıldığında, config değerleri yerine settings değerleri kullanılır.

#### Fiyat Dönüşümü

PayTR API fiyatları kuruş (cents) cinsinden ister. Paket otomatik olarak TL cinsindeki fiyatı kuruşa çevirir:

```php
// 1500.00 TL otomatik olarak 150000 kuruşa dönüştürülür
$data = CreateLinkData::from([
    'name' => 'Product',
    'price' => 1500.00, // TL
    // ...
]);

```
#### Exception Handling

```php
use FurkanMeclis\PayTRLink\Exceptions\PayTRRequestException;
use FurkanMeclis\PayTRLink\Exceptions\PayTRValidationException;

try {
    $response = PayTRLink::create($data);
} catch (PayTRRequestException $e) {
    // API isteği başarısız
    logger()->error('PayTR API Error', [
        'message' => $e->getMessage(),
        'response' => $e->response,
    ]);
} catch (PayTRValidationException $e) {
    // Validasyon hatası
    logger()->error('PayTR Validation Error', [
        'errors' => $e->errors,
    ]);
}

```
#### Test

```bash
composer test

```
Coverage ile test:

```bash
composer test-coverage

```
#### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

#### Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

#### Security Vulnerabilities

If you discover a security vulnerability, please send an e-mail to furkanmeclis@icloud.com. All security vulnerabilities will be promptly addressed.

#### Credits

- [Furkan Meclis](https://github.com/furkanmeclis)
- [All Contributors](../../contributors)

#### License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## V0.1 Beta - 2025-12-27

**Full Changelog**: https://github.com/furkanmeclis/paytr-link/compare/v0_beta...v0.1_beta

## V0 Beta - 2025-12-27

**Full Changelog**: https://github.com/furkanmeclis/paytr-link-api/commits/v0_beta

## 1.0.0 - 2025-01-XX

### Added

- Initial release
- PayTR Link API integration
- Create payment link functionality
- Delete payment link functionality
- Send SMS functionality
- Send email functionality
- Callback validation
- Spatie Laravel Data integration for type-safe DTOs
- Spatie Laravel Settings integration
- Facade support
- Comprehensive test coverage
- Support for PHP 8.4+
