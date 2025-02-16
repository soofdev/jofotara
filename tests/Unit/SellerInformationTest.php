<?php

use JBadarneh\JoFotara\Sections\SellerInformation;

beforeEach(function () {
    // Clear any configured defaults before each test
    SellerInformation::clearDefaults();
});

test('it can set seller information using fluent API', function () {
    $seller = new SellerInformation();
    $seller->setTin('12345678')
           ->setName('Test Company');

    $data = $seller->toArray();

    expect($data)
        ->toHaveKey('tin', '12345678')
        ->toHaveKey('name', 'Test Company')
        ->toHaveKey('countryCode', 'JO');
});

test('it uses configured defaults', function () {
    SellerInformation::configureDefaults(
        tin: '87654321',
        name: 'Default Company'
    );

    $seller = new SellerInformation();
    $data = $seller->toArray();

    expect($data)
        ->toHaveKey('tin', '87654321')
        ->toHaveKey('name', 'Default Company')
        ->toHaveKey('countryCode', 'JO');
});

test('it can override defaults using fluent API', function () {
    SellerInformation::configureDefaults(
        tin: '87654321',
        name: 'Default Company'
    );

    $seller = new SellerInformation();
    $seller->setName('Custom Company');
    $data = $seller->toArray();

    expect($data)
        ->toHaveKey('tin', '87654321')
        ->toHaveKey('name', 'Custom Company')
        ->toHaveKey('countryCode', 'JO');
});

test('it validates input is not empty', function () {
    expect(fn() => SellerInformation::configureDefaults('', 'Test Company'))
        ->toThrow(InvalidArgumentException::class, 'TIN cannot be empty');

    expect(fn() => SellerInformation::configureDefaults('12345678', ''))
        ->toThrow(InvalidArgumentException::class, 'Seller name cannot be empty');

    expect(fn() => SellerInformation::configureDefaults('12345678', '   '))
        ->toThrow(InvalidArgumentException::class, 'Seller name cannot be empty');
});