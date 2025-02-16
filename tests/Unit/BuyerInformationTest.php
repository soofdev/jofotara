<?php

use JBadarneh\JoFotara\Sections\BuyerInformation;

test('it validates city codes', function () {
    $buyer = new BuyerInformation;

    // Valid city code should work
    expect(fn () => $buyer->setCityCode('JO-AM'))->not->toThrow(InvalidArgumentException::class);

    // Invalid city code should throw
    expect(fn () => $buyer->setCityCode('JO-XX'))->toThrow(
        InvalidArgumentException::class,
        'City code must be one of: JO-BA, JO-MN, JO-MD, JO-MA, JO-KA, JO-JA, JO-IR, JO-AZ, JO-AT, JO-AQ, JO-AM, JO-AJ'
    );
});

test('it validates ID types', function () {
    $buyer = new BuyerInformation;

    // Valid ID types should work
    expect(fn () => $buyer->setId('123456789', 'NIN'))->not->toThrow(InvalidArgumentException::class);
    expect(fn () => $buyer->setId('P123456', 'PN'))->not->toThrow(InvalidArgumentException::class);
    expect(fn () => $buyer->setId('987654321', 'TIN'))->not->toThrow(InvalidArgumentException::class);

    // Invalid ID type should throw
    expect(fn () => $buyer->setId('123456789', 'XXX'))->toThrow(
        InvalidArgumentException::class,
        'ID type must be one of: NIN, PN, TIN'
    );
});

test('it returns array representation with all fields', function () {
    $buyer = new BuyerInformation;
    $buyer->setId('123456789', 'NIN')
        ->setPostalCode('11937')
        ->setCityCode('JO-AM')
        ->setName('John Doe')
        ->setPhone('0791234567')
        ->setTin('987654321');

    expect($buyer->toArray())->toBe([
        'id' => '123456789',
        'idType' => 'NIN',
        'postalCode' => '11937',
        'cityCode' => 'JO-AM',
        'name' => 'John Doe',
        'phone' => '0791234567',
        'tin' => '987654321',
    ]);
});

test('it returns array representation with minimal fields', function () {
    $buyer = new BuyerInformation;
    $buyer->setId('123456789', 'NIN');

    expect($buyer->toArray())->toBe([
        'id' => '123456789',
        'idType' => 'NIN',
        'postalCode' => null,
        'cityCode' => null,
        'name' => null,
        'phone' => null,
        'tin' => null,
    ]);
});
