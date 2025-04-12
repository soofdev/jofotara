<?php

use JBadarneh\JoFotara\Sections\InvoiceItems;

test('it can add and retrieve items', function () {
    $items = new InvoiceItems;

    $item = $items->addItem('1')
        ->setQuantity(2)
        ->setUnitPrice(10.0)
        ->setDescription('Test Item')
        ->setDiscount(2.0);

    $data = $items->toArray();

    expect($data)->toHaveKey('1')
        ->and($data['1'])->toMatchArray([
            'id' => '1',
            'quantity' => 2.0,
            'unitPrice' => 10.0,
            'discount' => 2.0,
            'description' => 'Test Item',
            'taxCategory' => 'S',
            'taxPercent' => 16.0,
            'unitCode' => 'PCE',
        ]);
});

test('it prevents duplicate item IDs', function () {
    $items = new InvoiceItems;

    $items->addItem('1');

    expect(fn () => $items->addItem('1'))
        ->toThrow(InvalidArgumentException::class, 'Item with ID 1 already exists');
});

test('it validates quantity is positive', function () {
    $items = new InvoiceItems;

    expect(fn () => $items->addItem('1')->setQuantity(0))
        ->toThrow(InvalidArgumentException::class, 'Quantity must be greater than 0')
        ->and(fn () => $items->addItem('2')->setQuantity(-1))
        ->toThrow(InvalidArgumentException::class, 'Quantity must be greater than 0');
});

test('it validates unit price is not negative', function () {
    $items = new InvoiceItems;

    expect(fn () => $items->addItem('1')->setUnitPrice(-1))
        ->toThrow(InvalidArgumentException::class, 'Unit price cannot be negative');
});

test('it validates discount is not negative', function () {
    $items = new InvoiceItems;

    expect(fn () => $items->addItem('1')->setDiscount(-1))
        ->toThrow(InvalidArgumentException::class, 'Discount amount cannot be negative');
});

test('it validates discount is not greater than total amount', function () {
    $items = new InvoiceItems;

    $item = $items->addItem('1')
        ->setQuantity(2)
        ->setUnitPrice(10.0);

    expect(fn () => $item->setDiscount(21))
        ->toThrow(InvalidArgumentException::class, 'Discount cannot be greater than total amount');
});

test('it validates tax category and percentage', function () {
    $items = new InvoiceItems;

    expect(fn () => $items->addItem('1')->setTaxCategory('X'))
        ->toThrow(InvalidArgumentException::class, 'Tax category must be Z, O, or S')
        ->and(fn () => $items->addItem('2')->setTaxCategory('S', 0))
        ->toThrow(InvalidArgumentException::class, 'Invalid tax rate for standard category')
        ->and(fn () => $items->addItem('3')->setTaxCategory('S'))
        ->toThrow(InvalidArgumentException::class, 'Tax percentage is required for standard rate category');

    // Valid cases
    $item = $items->addItem('4')->setTaxCategory('S', 16);
    expect($item->toArray()['taxPercent'])->toBe(16.0);

    $item = $items->addItem('5')->setTaxCategory('Z');
    expect($item->toArray()['taxPercent'])->toBe(0.0);
});

test('it defaults to standard rate tax category', function () {
    $items = new InvoiceItems;

    $item = $items->addItem('1');

    expect($item->toArray())
        ->toMatchArray([
            'id' => '1',
            'taxCategory' => 'S',
            'taxPercent' => 16.0,
        ]);
});

test('it can set tax exempted status', function () {
    $items = new InvoiceItems;

    $item = $items->addItem('1')->taxExempted();

    expect($item->toArray())
        ->toMatchArray([
            'taxCategory' => 'Z',
            'taxPercent' => 0.0,
        ]);
});

test('it can set zero tax rate', function () {
    $items = new InvoiceItems;

    $item = $items->addItem('1')->zeroTax();

    expect($item->toArray())
        ->toMatchArray([
            'taxCategory' => 'O',
            'taxPercent' => 0.0,
        ]);
});

test('it can set standard tax rate', function () {
    $items = new InvoiceItems;

    $item = $items->addItem('1')->tax(7);

    expect($item->toArray())
        ->toMatchArray([
            'taxCategory' => 'S',
            'taxPercent' => 7.0,
        ]);
});

test('it validates tax rate in tax() method', function () {
    $items = new InvoiceItems;

    expect(fn () => $items->addItem('1')->tax(0))
        ->toThrow(InvalidArgumentException::class, 'Invalid tax rate for standard category');
});

test('it calculates tax exclusive amount correctly', function () {
    $items = new InvoiceItems;

    $item = $items->addItem('1')
        ->setQuantity(2)
        ->setUnitPrice(100)
        ->setDiscount(20);
    // (2 * 100) = 200
    expect($item->getAmountBeforeDiscount())->toBe(200.0);
});

test('it calculates tax amount correctly for different tax categories', function () {
    $items = new InvoiceItems;

    // Standard rate (16%)
    $standardItem = $items->addItem('1')
        ->setQuantity(2)
        ->setUnitPrice(100)
        ->setDiscount(20)
        ->tax(16);

    // 180 * 0.16 = 28.8
    expect($standardItem->getTaxAmount())->toBe(28.8);

    // Zero rated
    $zeroRatedItem = $items->addItem('2')
        ->setQuantity(2)
        ->setUnitPrice(100)
        ->zeroTax();

    expect($zeroRatedItem->getTaxAmount())->toBe(0.0);

    // Exempted
    $exemptedItem = $items->addItem('3')
        ->setQuantity(2)
        ->setUnitPrice(100)
        ->taxExempted();

    expect($exemptedItem->getTaxAmount())->toBe(0.0);
});

test('it calculates tax inclusive amount correctly', function () {
    $items = new InvoiceItems;

    $item = $items->addItem('1')
        ->setQuantity(2)
        ->setUnitPrice(100)
        ->setDiscount(20)
        ->tax(16);

    // Tax exclusive = (2 * 100) - 20 = 180
    // Tax amount = 180 * 0.16 = 28.8
    // Tax inclusive = 180 + 28.8 = 208.8
    expect($item->getTaxInclusiveAmount())->toBe(208.8);
});

test('it throws exception when calculating amounts without required fields', function () {
    $items = new InvoiceItems;
    $item = $items->addItem('1');

    expect(fn () => $item->getAmountBeforeDiscount())
        ->toThrow(InvalidArgumentException::class, 'Quantity is required to calculate tax exclusive amount');

    $item->setQuantity(1);
    expect(fn () => $item->getAmountBeforeDiscount())
        ->toThrow(InvalidArgumentException::class, 'Unit price is required to calculate tax exclusive amount');
});
