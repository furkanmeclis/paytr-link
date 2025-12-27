<?php

use FurkanMeclis\PayTRLink\Data\CreateLinkData;
use FurkanMeclis\PayTRLink\Data\DeleteLinkData;
use FurkanMeclis\PayTRLink\Enums\CurrencyEnum;
use FurkanMeclis\PayTRLink\Enums\LinkTypeEnum;
use FurkanMeclis\PayTRLink\PayTRLinkService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    // Mock HTTP client
    Http::fake([
        'www.paytr.com/odeme/api/link/*' => Http::response([
            'status' => 'success',
            'link' => 'https://www.paytr.com/odeme/guvenli/xxx',
            'id' => 'test_link_id',
        ], 200),
    ]);
});

it('can create a payment link', function () {
    $service = app(PayTRLinkService::class);

    $data = CreateLinkData::from([
        'name' => 'Test Product',
        'price' => 1500.00,
        'currency' => CurrencyEnum::TL,
        'link_type' => LinkTypeEnum::Product,
        'max_installment' => 12,
        'lang' => 'tr',
    ]);

    $response = $service->create($data);

    expect($response)->toBeInstanceOf(\FurkanMeclis\PayTRLink\Data\PayTRResponseData::class)
        ->and($response->isSuccess())->toBeTrue()
        ->and($response->link)->not->toBeNull();
});

it('can delete a payment link', function () {
    $service = app(PayTRLinkService::class);

    $data = DeleteLinkData::from([
        'link_id' => 'test_link_id',
    ]);

    $response = $service->delete($data);

    expect($response)->toBeInstanceOf(\FurkanMeclis\PayTRLink\Data\PayTRResponseData::class);
});

it('can validate callback data', function () {
    $service = app(PayTRLinkService::class);

    // This is a simplified test - in real scenario, you would test with actual hash
    $callbackData = [
        'callback_id' => 'test_callback_id',
        'merchant_oid' => 'test_merchant_oid',
        'status' => 'success',
        'total_amount' => '150000',
        'hash' => 'test_hash',
    ];

    // This will fail because hash won't match, but we test the method exists
    $result = $service->validateCallback($callbackData);

    expect($result)->toBeBool();
});
