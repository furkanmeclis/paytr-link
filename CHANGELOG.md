# Changelog

All notable changes to `furkanmeclis/paytr-link` will be documented in this file.

## V0.12 - 2025-12-27

**Full Changelog**: https://github.com/furkanmeclis/paytr-link/compare/v0.11...v0.12

## V0.11 - 2025-12-27

### PayTR Link API Laravel Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/furkanmeclis/paytr-link.svg?style=flat-square)](https://packagist.org/packages/furkanmeclis/paytr-link)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/furkanmeclis/paytr-link/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/furkanmeclis/paytr-link/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/furkanmeclis/paytr-link.svg?style=flat-square)](https://packagist.org/packages/furkanmeclis/paytr-link)

Laravel package for PayTR Link API integration. This package allows you to easily integrate with PayTR Link API.

#### Features

- ✅ All PayTR Link API endpoints
- ✅ Type-safe Data Transfer Objects (Spatie Laravel Data)
- ✅ Settings management (Spatie Laravel Settings)
- ✅ Event system (Link creation, deletion, SMS/Email sending, Callback)
- ✅ Facade support for easy usage
- ✅ Comprehensive test coverage
- ✅ PHP 8.1+ support

#### Installation

Install the package via Composer:

```bash
composer require furkanmeclis/paytr-link


```
Publish the config file:

```bash
php artisan vendor:publish --tag="paytr-link-config"


```
If you're going to use Spatie Laravel Settings:

```bash
php artisan vendor:publish --provider="Spatie\LaravelSettings\LaravelSettingsServiceProvider" --tag="migrations"
php artisan migrate


```
#### Configuration

Add your PayTR credentials to your `.env` file:

```env
PAYTR_MERCHANT_ID=your_merchant_id
PAYTR_MERCHANT_KEY=your_merchant_key
PAYTR_MERCHANT_SALT=your_merchant_salt
PAYTR_DEBUG_ON=1


```
You can also configure via the config file (`config/paytr-link.php`).

#### Usage

##### Creating a Link

```php
use FurkanMeclis\PayTRLink\Facades\PayTRLink;
use FurkanMeclis\PayTRLink\Data\CreateLinkData;
use FurkanMeclis\PayTRLink\Enums\CurrencyEnum;
use FurkanMeclis\PayTRLink\Enums\LinkTypeEnum;

$data = CreateLinkData::from([
    'name' => 'Web Design Service',
    'price' => 1500.00, // in TL
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
##### Creating a Collection Link

```php
$data = CreateLinkData::from([
    'name' => 'Bulk Payment',
    'price' => 5000.00,
    'currency' => CurrencyEnum::TL,
    'link_type' => LinkTypeEnum::Collection,
    'email' => 'customer@example.com', // Required for Collection
    'max_installment' => 12,
]);

$response = PayTRLink::create($data);


```
##### Deleting a Link

```php
use FurkanMeclis\PayTRLink\Data\DeleteLinkData;

$response = PayTRLink::delete(DeleteLinkData::from([
    'link_id' => 'link_id_here',
]));

// Or directly with a string
$response = PayTRLink::delete('link_id_here');


```
##### Sending SMS

```php
use FurkanMeclis\PayTRLink\Data\SendSmsData;

$response = PayTRLink::sendSms(SendSmsData::from([
    'link_id' => 'link_id_here',
    'phone' => '5551234567',
]));


```
##### Sending Email

```php
use FurkanMeclis\PayTRLink\Data\SendEmailData;

$response = PayTRLink::sendEmail(SendEmailData::from([
    'link_id' => 'link_id_here',
    'email' => 'customer@example.com',
]));


```
##### Callback Validation

```php
use Illuminate\Http\Request;

public function handleCallback(Request $request)
{
    if (PayTRLink::validateCallback($request->all())) {
        // Transaction successful
        $callbackData = \FurkanMeclis\PayTRLink\Data\CallbackData::from($request->all());
        
        if ($callbackData->status === 'success') {
            // Payment successful, update the transaction
            echo "OK";
        } else {
            // Payment failed
            echo "OK";
        }
    } else {
        return response('Invalid hash', 400);
    }
}


```
##### Service Injection

You can also use dependency injection instead of Facade:

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
#### Spatie Laravel Settings Integration

The package works integrated with Spatie Laravel Settings. You can store settings in the database using Settings:

##### Setting Up Settings

First, set up the settings structure in the database:

```bash
php artisan paytr-link:setup-settings


```
This will create the necessary settings entries in the database. To also initialize settings with values from your config file, use the `--init` flag:

```bash
php artisan paytr-link:setup-settings --init


```
##### Using Settings

```php
use FurkanMeclis\PayTRLink\Settings\PayTRSettings;

// Assign values to Settings
$settings = app(PayTRSettings::class);
$settings->merchant_id = 'your_merchant_id';
$settings->merchant_key = 'your_merchant_key';
$settings->merchant_salt = 'your_merchant_salt';
$settings->debug_on = true; // or false
$settings->save();

// Reading values from Settings (also reads from config with fallback)
$settings = app(PayTRSettings::class);
$merchantId = $settings->getMerchantId();
$merchantKey = $settings->getMerchantKey();
$merchantSalt = $settings->getMerchantSalt();
$debugMode = $settings->getDebugOn();


```
When Settings is used, settings values are used instead of config values. The `getMerchantId()`, `getMerchantKey()`, `getMerchantSalt()`, and `getDebugOn()` methods automatically read from config if no value exists in settings.

#### Price Conversion

PayTR API expects prices in kuruş (cents). The package automatically converts TL prices to kuruş:

```php
// 1500.00 TL is automatically converted to 150000 kuruş
$data = CreateLinkData::from([
    'name' => 'Product',
    'price' => 1500.00, // TL
    // ...
]);


```
#### Events

The package dispatches events for various operations. You can track operations and perform actions when needed by listening to these events:

##### Event List

- `FurkanMeclis\PayTRLink\Events\LinkCreated` - When a link is created
- `FurkanMeclis\PayTRLink\Events\LinkDeleted` - When a link is deleted
- `FurkanMeclis\PayTRLink\Events\SmsSent` - When SMS is sent
- `FurkanMeclis\PayTRLink\Events\EmailSent` - When email is sent
- `FurkanMeclis\PayTRLink\Events\CallbackReceived` - When callback is received

##### Event Usage

###### Creating an Event Listener

Create a listener in the `app/Listeners` folder:

```php
// app/Listeners/HandleLinkCreated.php
namespace App\Listeners;

use FurkanMeclis\PayTRLink\Events\LinkCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleLinkCreated implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(LinkCreated $event): void
    {
        $linkData = $event->createLinkData;
        $response = $event->response;

        // Actions to perform when link is created
        if ($response->isSuccess()) {
            logger()->info('Link created successfully', [
                'link_id' => $response->id,
                'link' => $response->link,
                'name' => $linkData->name,
                'price' => $linkData->price,
            ]);

            // Example: Save to database
            // Link::create([
            //     'paytr_link_id' => $response->id,
            //     'link' => $response->link,
            //     ...
            // ]);
        }
    }
}


```
###### Registering in Event Service Provider

In the `app/Providers/EventServiceProvider.php` file:

```php
use App\Listeners\HandleLinkCreated;
use FurkanMeclis\PayTRLink\Events\LinkCreated;
use FurkanMeclis\PayTRLink\Events\LinkDeleted;
use FurkanMeclis\PayTRLink\Events\SmsSent;
use FurkanMeclis\PayTRLink\Events\EmailSent;
use FurkanMeclis\PayTRLink\Events\CallbackReceived;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        LinkCreated::class => [
            HandleLinkCreated::class,
        ],
        LinkDeleted::class => [
            // Your listeners
        ],
        SmsSent::class => [
            // Your listeners
        ],
        EmailSent::class => [
            // Your listeners
        ],
        CallbackReceived::class => [
            // Your listeners
        ],
    ];
}


```
###### Callback Event Usage

```php
// app/Listeners/HandleCallbackReceived.php
namespace App\Listeners;

use FurkanMeclis\PayTRLink\Events\CallbackReceived;

class HandleCallbackReceived
{
    public function handle(CallbackReceived $event): void
    {
        $callbackData = $event->callbackData;
        $isValid = $event->isValid;

        if ($isValid && $callbackData->status === 'success') {
            // Payment successful - update order
            logger()->info('Payment successful', [
                'merchant_oid' => $callbackData->merchant_oid,
                'total_amount' => $callbackData->total_amount,
            ]);
        } else {
            // Payment failed
            logger()->warning('Payment failed', [
                'merchant_oid' => $callbackData->merchant_oid,
                'status' => $callbackData->status,
            ]);
        }
    }
}


```
###### Listening to Events with Closures

You can use closures instead of creating event listeners:

```php
use FurkanMeclis\PayTRLink\Events\LinkCreated;
use Illuminate\Support\Facades\Event;

Event::listen(LinkCreated::class, function (LinkCreated $event) {
    if ($event->response->isSuccess()) {
        // Actions to perform when link is created
        logger()->info('Link created: ' . $event->response->link);
    }
});


```
#### Exception Handling

```php
use FurkanMeclis\PayTRLink\Exceptions\PayTRRequestException;
use FurkanMeclis\PayTRLink\Exceptions\PayTRValidationException;

try {
    $response = PayTRLink::create($data);
} catch (PayTRRequestException $e) {
    // API request failed
    logger()->error('PayTR API Error', [
        'message' => $e->getMessage(),
        'response' => $e->response,
    ]);
} catch (PayTRValidationException $e) {
    // Validation error
    logger()->error('PayTR Validation Error', [
        'errors' => $e->errors,
    ]);
}


```
#### Testing

```bash
composer test


```
Test with coverage:

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

## V0.10 - 2025-12-27

**Full Changelog**: https://github.com/furkanmeclis/paytr-link/compare/v0.9...v0.10

## V0.9 - 2025-12-27

**Full Changelog**: https://github.com/furkanmeclis/paytr-link/compare/v0.8...v0.9

## V0.8 - 2025-12-27

**Full Changelog**: https://github.com/furkanmeclis/paytr-link/compare/v0.7...v0.8

## V0.7 - 2025-12-27

**Full Changelog**: https://github.com/furkanmeclis/paytr-link/compare/V0.6...v0.7

## V0.6 - 2025-12-27

**Full Changelog**: https://github.com/furkanmeclis/paytr-link/compare/v0.5...V0.6

## V0.5 - 2025-12-27

**Full Changelog**: https://github.com/furkanmeclis/paytr-link/compare/v0.3...v0.5

## V0.4 - 2025-12-27

**Full Changelog**: https://github.com/furkanmeclis/paytr-link/compare/v0.3...v0.4

## V0.3 - 2025-12-27

**Full Changelog**: https://github.com/furkanmeclis/paytr-link/compare/v0.2...v0.3

## V0.2 - 2025-12-27

### PayTR Link API Laravel Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/furkanmeclis/paytr-link.svg?style=flat-square)](https://packagist.org/packages/furkanmeclis/paytr-link)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/furkanmeclis/paytr-link/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/furkanmeclis/paytr-link/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/furkanmeclis/paytr-link.svg?style=flat-square)](https://packagist.org/packages/furkanmeclis/paytr-link)

Laravel package for PayTR Link API integration. This package allows you to easily integrate with PayTR Link API.

#### Features

- ✅ All PayTR Link API endpoints
- ✅ Type-safe Data Transfer Objects (Spatie Laravel Data)
- ✅ Settings management (Spatie Laravel Settings)
- ✅ Facade support for easy usage
- ✅ Comprehensive test coverage
- ✅ PHP 8.1+ support

#### Installation

Install the package via Composer:

```bash
composer require furkanmeclis/paytr-link











```
Publish the config file:

```bash
php artisan vendor:publish --tag="paytr-link-config"











```
**Note**: Migration is not required for the package to work. If you want to store links in the database, you can use the migration stub file.

If you're going to use Spatie Laravel Settings:

```bash
php artisan vendor:publish --provider="Spatie\LaravelSettings\LaravelSettingsServiceProvider" --tag="migrations"
php artisan migrate











```
#### Configuration

Add your PayTR credentials to your `.env` file:

```env
PAYTR_MERCHANT_ID=your_merchant_id
PAYTR_MERCHANT_KEY=your_merchant_key
PAYTR_MERCHANT_SALT=your_merchant_salt
PAYTR_DEBUG_ON=1











```
You can also configure via the config file (`config/paytr-link.php`).

#### Usage

##### Creating a Link

```php
use FurkanMeclis\PayTRLink\Facades\PayTRLink;
use FurkanMeclis\PayTRLink\Data\CreateLinkData;
use FurkanMeclis\PayTRLink\Enums\CurrencyEnum;
use FurkanMeclis\PayTRLink\Enums\LinkTypeEnum;

$data = CreateLinkData::from([
    'name' => 'Web Design Service',
    'price' => 1500.00, // in TL
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
##### Creating a Collection Link

```php
$data = CreateLinkData::from([
    'name' => 'Bulk Payment',
    'price' => 5000.00,
    'currency' => CurrencyEnum::TL,
    'link_type' => LinkTypeEnum::Collection,
    'email' => 'customer@example.com', // Required for Collection
    'max_installment' => 12,
]);

$response = PayTRLink::create($data);











```
##### Deleting a Link

```php
use FurkanMeclis\PayTRLink\Data\DeleteLinkData;

$response = PayTRLink::delete(DeleteLinkData::from([
    'link_id' => 'link_id_here',
]));

// Or directly with a string
$response = PayTRLink::delete('link_id_here');











```
##### Sending SMS

```php
use FurkanMeclis\PayTRLink\Data\SendSmsData;

$response = PayTRLink::sendSms(SendSmsData::from([
    'link_id' => 'link_id_here',
    'phone' => '5551234567',
]));











```
##### Sending Email

```php
use FurkanMeclis\PayTRLink\Data\SendEmailData;

$response = PayTRLink::sendEmail(SendEmailData::from([
    'link_id' => 'link_id_here',
    'email' => 'customer@example.com',
]));











```
##### Callback Validation

```php
use Illuminate\Http\Request;

public function handleCallback(Request $request)
{
    if (PayTRLink::validateCallback($request->all())) {
        // Transaction successful
        $callbackData = \FurkanMeclis\PayTRLink\Data\CallbackData::from($request->all());
        
        if ($callbackData->status === 'success') {
            // Payment successful, update the transaction
            echo "OK";
        } else {
            // Payment failed
            echo "OK";
        }
    } else {
        return response('Invalid hash', 400);
    }
}











```
##### Service Injection

You can also use dependency injection instead of Facade:

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
#### Spatie Laravel Settings Integration

The package works integrated with Spatie Laravel Settings. You can store settings in the database using Settings:

```php
use FurkanMeclis\PayTRLink\Settings\PayTRSettings;

$settings = app(PayTRSettings::class);
$settings->merchant_id = 'your_merchant_id';
$settings->merchant_key = 'your_merchant_key';
$settings->merchant_salt = 'your_merchant_salt';
$settings->debug_on = true;
$settings->save();











```
When Settings is used, settings values are used instead of config values.

#### Price Conversion

PayTR API expects prices in kuruş (cents). The package automatically converts TL prices to kuruş:

```php
// 1500.00 TL is automatically converted to 150000 kuruş
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
    // API request failed
    logger()->error('PayTR API Error', [
        'message' => $e->getMessage(),
        'response' => $e->response,
    ]);
} catch (PayTRValidationException $e) {
    // Validation error
    logger()->error('PayTR Validation Error', [
        'errors' => $e->errors,
    ]);
}











```
#### Test

```bash
composer test











```
Test with coverage:

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
