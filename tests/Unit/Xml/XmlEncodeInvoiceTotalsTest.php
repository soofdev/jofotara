<?php

use JBadarneh\JoFotara\Sections\InvoiceTotals;
use JBadarneh\JoFotara\Traits\XmlHelperTrait;

uses(XmlHelperTrait::class);

test('it requires tax exclusive amount', function () {
    $totals = new InvoiceTotals;
    $totals->setTaxInclusiveAmount(110)
        ->setTaxTotalAmount(10)
        ->setPayableAmount(110);

    expect(fn () => $totals->toXml())->toThrow(
        InvalidArgumentException::class,
        'Tax exclusive amount is required'
    );
});

test('it requires tax inclusive amount', function () {
    $totals = new InvoiceTotals;
    $totals->setTaxExclusiveAmount(100)
        ->setTaxTotalAmount(10)
        ->setPayableAmount(110);

    expect(fn () => $totals->toXml())->toThrow(
        InvalidArgumentException::class,
        'Tax inclusive amount is required'
    );
});

test('it requires payable amount', function () {
    $totals = new InvoiceTotals;
    $totals->setTaxExclusiveAmount(100)
        ->setTaxInclusiveAmount(110)
        ->setTaxTotalAmount(10);

    expect(fn () => $totals->toXml())->toThrow(
        InvalidArgumentException::class,
        'Payable amount is required'
    );
});

test('it generates exact XML structure with discount', function () {
    $totals = new InvoiceTotals;
    $totals->setTaxExclusiveAmount(100)
        ->setTaxInclusiveAmount(110)
        ->setDiscountTotalAmount(10)
        ->setTaxTotalAmount(10)
        ->setPayableAmount(100);

    $expected = $this->normalizeXml(<<<'XML'
<cac:AllowanceCharge>
    <cbc:ChargeIndicator>false</cbc:ChargeIndicator>
    <cbc:AllowanceChargeReason>discount</cbc:AllowanceChargeReason>
    <cbc:Amount currencyID="JOD">10.000000000</cbc:Amount>
</cac:AllowanceCharge>
<cac:TaxTotal>
    <cbc:TaxAmount currencyID="JOD">10.000000000</cbc:TaxAmount>
</cac:TaxTotal>
<cac:LegalMonetaryTotal>
    <cbc:TaxExclusiveAmount currencyID="JOD">100.000000000</cbc:TaxExclusiveAmount>
    <cbc:TaxInclusiveAmount currencyID="JOD">110.000000000</cbc:TaxInclusiveAmount>
    <cbc:AllowanceTotalAmount currencyID="JOD">10.000000000</cbc:AllowanceTotalAmount>
    <cbc:PayableAmount currencyID="JOD">100.000000000</cbc:PayableAmount>
</cac:LegalMonetaryTotal>
XML);

    expect($totals->toXml())->toBe($expected);
});

test('it generates XML structure without discount', function () {
    $totals = new InvoiceTotals;
    $totals->setTaxExclusiveAmount(100)
        ->setTaxInclusiveAmount(116)
        ->setTaxTotalAmount(16)
        ->setPayableAmount(116);

    $expected = $this->normalizeXml(<<<'XML'
<cac:TaxTotal>
    <cbc:TaxAmount currencyID="JOD">16.000000000</cbc:TaxAmount>
</cac:TaxTotal>
<cac:LegalMonetaryTotal>
    <cbc:TaxExclusiveAmount currencyID="JOD">100.000000000</cbc:TaxExclusiveAmount>
    <cbc:TaxInclusiveAmount currencyID="JOD">116.000000000</cbc:TaxInclusiveAmount>
    <cbc:PayableAmount currencyID="JOD">116.000000000</cbc:PayableAmount>
</cac:LegalMonetaryTotal>
XML);

    expect($totals->toXml())->toBe($expected);
});
