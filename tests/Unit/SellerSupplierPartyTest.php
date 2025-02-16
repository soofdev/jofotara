<?php

use JBadarneh\JoFotara\Sections\SellerSupplierParty;

test('it requires sequence of income source', function () {
    $supplier = new SellerSupplierParty;

    expect($supplier->toArray())->toBe([
        'sequenceId' => null,
    ]);

    expect(fn () => $supplier->toXml())->toThrow(
        InvalidArgumentException::class,
        'Sequence of income source is required'
    );
});

test('it returns array representation', function () {
    $supplier = new SellerSupplierParty;
    $supplier->setSequenceId('9932895');

    expect($supplier->toArray())->toBe([
        'sequenceId' => '9932895',
    ]);
});
