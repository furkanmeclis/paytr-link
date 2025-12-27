<?php

use FurkanMeclis\PayTRLink\Support\TokenGenerator;

it('can generate token for create link', function () {
    $data = [
        'name' => 'Test Product',
        'price' => '150000',
        'currency' => 'TL',
        'max_installment' => '12',
        'link_type' => 'product',
        'lang' => 'tr',
        'min_count' => '1',
    ];

    $token = TokenGenerator::forCreateLink($data, 'test_key', 'test_salt');

    expect($token)->toBeString()
        ->not->toBeEmpty();
});

it('can generate token for delete link', function () {
    $token = TokenGenerator::forDeleteLink('link123', 'merchant123', 'test_key', 'test_salt');

    expect($token)->toBeString()
        ->not->toBeEmpty();
});

it('can generate token for send sms', function () {
    $token = TokenGenerator::forSendSms('link123', 'merchant123', '5551234567', 'test_key', 'test_salt');

    expect($token)->toBeString()
        ->not->toBeEmpty();
});

it('can generate token for send email', function () {
    $token = TokenGenerator::forSendEmail('link123', 'merchant123', 'test@example.com', 'test_key', 'test_salt');

    expect($token)->toBeString()
        ->not->toBeEmpty();
});
