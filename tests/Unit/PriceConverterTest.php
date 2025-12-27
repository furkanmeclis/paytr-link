<?php

use FurkanMeclis\PayTRLink\Support\PriceConverter;

it('can convert price to kuruş', function () {
    $kurus = PriceConverter::toKurus(1500.50);

    expect($kurus)->toBe(150050);
});

it('can convert kuruş to price', function () {
    $price = PriceConverter::fromKurus(150050);

    expect($price)->toBe(1500.50);
});

it('handles integer prices correctly', function () {
    $kurus = PriceConverter::toKurus(1500);

    expect($kurus)->toBe(150000);
});
