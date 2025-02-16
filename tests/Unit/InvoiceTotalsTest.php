<?php

use JBadarneh\JoFotara\Sections\InvoiceTotals;

test('it validates tax exclusive amount', function () {
    $totals = new InvoiceTotals;

    expect(fn () => $totals->setTaxExclusiveAmount(-1))->toThrow(
        InvalidArgumentException::class,
        'Tax exclusive amount cannot be negative'
    );
});

test('it validates tax inclusive amount', function () {
    $totals = new InvoiceTotals;
    $totals->setTaxExclusiveAmount(100);

    expect(fn () => $totals->setTaxInclusiveAmount(-1))->toThrow(
        InvalidArgumentException::class,
        'Tax inclusive amount cannot be negative'
    );

    expect(fn () => $totals->setTaxInclusiveAmount(90))->toThrow(
        InvalidArgumentException::class,
        'Tax inclusive amount cannot be less than tax exclusive amount'
    );
});

test('it validates discount total amount', function () {
    $totals = new InvoiceTotals;
    $totals->setTaxExclusiveAmount(100);

    expect(fn () => $totals->setDiscountTotalAmount(-1))->toThrow(
        InvalidArgumentException::class,
        'Discount total amount cannot be negative'
    );

    expect(fn () => $totals->setDiscountTotalAmount(101))->toThrow(
        InvalidArgumentException::class,
        'Discount total amount cannot be greater than tax exclusive amount'
    );
});

test('it validates tax total amount', function () {
    $totals = new InvoiceTotals;
    $totals->setTaxExclusiveAmount(100)
        ->setTaxInclusiveAmount(110);

    expect(fn () => $totals->setTaxTotalAmount(-1))->toThrow(
        InvalidArgumentException::class,
        'Tax total amount cannot be negative'
    );

    expect(fn () => $totals->setTaxTotalAmount(20))->toThrow(
        InvalidArgumentException::class,
        'Tax total amount would make tax inclusive amount invalid'
    );
});

test('it validates payable amount', function () {
    $totals = new InvoiceTotals;
    $totals->setTaxExclusiveAmount(100)
        ->setTaxInclusiveAmount(110)
        ->setDiscountTotalAmount(10);

    expect(fn () => $totals->setPayableAmount(-1))->toThrow(
        InvalidArgumentException::class,
        'Payable amount cannot be negative'
    );

    expect(fn () => $totals->setPayableAmount(90))->toThrow(
        InvalidArgumentException::class,
        'Payable amount cannot be less than tax inclusive amount minus allowances'
    );
});

test('it returns array representation', function () {
    $totals = new InvoiceTotals;
    $totals->setTaxExclusiveAmount(100)
        ->setTaxInclusiveAmount(110)
        ->setDiscountTotalAmount(10)
        ->setTaxTotalAmount(10)
        ->setPayableAmount(100);

    expect($totals->toArray())->toBe([
        'taxExclusiveAmount' => 100.0,
        'taxInclusiveAmount' => 110.0,
        'discountTotalAmount' => 10.0,
        'taxTotalAmount' => 10.0,
        'payableAmount' => 100.0,
    ]);
});
