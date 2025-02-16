<?php

use JBadarneh\JoFotara\Sections\SupplierIncomeSource;

test('it returns array representation', function () {
    $supplier = new SupplierIncomeSource('9932895');

    expect($supplier->toArray())->toBe([
        'sequenceId' => '9932895',
    ]);
});
