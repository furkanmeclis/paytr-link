<?php

use FurkanMeclis\PayTRLink\Data\CreateLinkData;
use FurkanMeclis\PayTRLink\Enums\CurrencyEnum;
use FurkanMeclis\PayTRLink\Enums\LinkTypeEnum;
use FurkanMeclis\PayTRLink\PayTRLinkService;
use Illuminate\Support\Facades\Http;

it('handles api errors correctly', function () {
    Http::fake([
        'www.paytr.com/odeme/api/link/*' => Http::response([
            'status' => 'failed',
            'message' => 'Invalid merchant credentials',
        ], 400),
    ]);

    $service = app(PayTRLinkService::class);

    $data = CreateLinkData::from([
        'name' => 'Test Product',
        'price' => 1500.00,
        'currency' => CurrencyEnum::TL,
        'link_type' => LinkTypeEnum::Product,
    ]);

    expect(fn () => $service->create($data))
        ->toThrow(\FurkanMeclis\PayTRLink\Exceptions\PayTRRequestException::class);
});

it('handles network errors', function () {
    Http::fake([
        'www.paytr.com/odeme/api/link/*' => Http::response('', 500),
    ]);

    $service = app(PayTRLinkService::class);

    $data = CreateLinkData::from([
        'name' => 'Test Product',
        'price' => 1500.00,
        'currency' => CurrencyEnum::TL,
        'link_type' => LinkTypeEnum::Product,
    ]);

    expect(fn () => $service->create($data))
        ->toThrow(\FurkanMeclis\PayTRLink\Exceptions\PayTRRequestException::class);
});
