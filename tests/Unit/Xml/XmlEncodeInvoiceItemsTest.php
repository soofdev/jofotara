<?php

use JBadarneh\JoFotara\Sections\InvoiceItems;
use JBadarneh\JoFotara\Traits\XmlHelperTrait;

uses(XmlHelperTrait::class);

test('it generates exact XML structure for standard rate item', function () {
    $items = new InvoiceItems;
    $items->addItem('1')
        ->setQuantity(33)
        ->setUnitPrice(2.0)
        ->setDescription('Biscuit')
        ->setDiscount(2.0)
        ->setTaxCategory('S', 7);

    $expected = $this->normalizeXml(<<<'XML'
<cac:InvoiceLine>
    <cbc:ID>1</cbc:ID>
    <cbc:InvoicedQuantity unitCode="PCE">33.000000000</cbc:InvoicedQuantity>
    <cbc:LineExtensionAmount currencyID="JOD">64.000000000</cbc:LineExtensionAmount>
    <cac:TaxTotal>
        <cbc:TaxAmount currencyID="JOD">4.480000000</cbc:TaxAmount>
        <cbc:RoundingAmount currencyID="JOD">68.480000000</cbc:RoundingAmount>
        <cac:TaxSubtotal>
            <cbc:TaxAmount currencyID="JOD">4.480000000</cbc:TaxAmount>
            <cac:TaxCategory>
                <cbc:ID schemeAgencyID="6" schemeID="UN/ECE 5305">S</cbc:ID>
                <cbc:Percent>7.000000000</cbc:Percent>
                <cac:TaxScheme>
                    <cbc:ID schemeAgencyID="6" schemeID="UN/ECE 5153">VAT</cbc:ID>
                </cac:TaxScheme>
            </cac:TaxCategory>
        </cac:TaxSubtotal>
    </cac:TaxTotal>
    <cac:Item>
        <cbc:Name>Biscuit</cbc:Name>
    </cac:Item>
    <cac:Price>
        <cbc:PriceAmount currencyID="JOD">2.000000000</cbc:PriceAmount>
        <cac:AllowanceCharge>
            <cbc:ChargeIndicator>false</cbc:ChargeIndicator>
            <cbc:AllowanceChargeReason>DISCOUNT</cbc:AllowanceChargeReason>
            <cbc:Amount currencyID="JOD">2.000000000</cbc:Amount>
        </cac:AllowanceCharge>
    </cac:Price>
</cac:InvoiceLine>
XML);

    expect($items->toXml())->toBe($expected);
});

test('it generates exact XML structure for tax exempted item', function () {
    $items = new InvoiceItems;
    $items->addItem('2')
        ->setQuantity(10)
        ->setUnitPrice(5.0)
        ->setDescription('Chocolate')
        ->setDiscount(0)
        ->setTaxCategory('Z');

    $expected = $this->normalizeXml(<<<'XML'
<cac:InvoiceLine>
    <cbc:ID>2</cbc:ID>
    <cbc:InvoicedQuantity unitCode="PCE">10.000000000</cbc:InvoicedQuantity>
    <cbc:LineExtensionAmount currencyID="JOD">50.000000000</cbc:LineExtensionAmount>
    <cac:TaxTotal>
        <cbc:TaxAmount currencyID="JOD">0.000000000</cbc:TaxAmount>
        <cbc:RoundingAmount currencyID="JOD">50.000000000</cbc:RoundingAmount>
        <cac:TaxSubtotal>
            <cbc:TaxAmount currencyID="JOD">0.000000000</cbc:TaxAmount>
            <cac:TaxCategory>
                <cbc:ID schemeAgencyID="6" schemeID="UN/ECE 5305">Z</cbc:ID>
                <cbc:Percent>0.000000000</cbc:Percent>
                <cac:TaxScheme>
                    <cbc:ID schemeAgencyID="6" schemeID="UN/ECE 5153">VAT</cbc:ID>
                </cac:TaxScheme>
            </cac:TaxCategory>
        </cac:TaxSubtotal>
    </cac:TaxTotal>
    <cac:Item>
        <cbc:Name>Chocolate</cbc:Name>
    </cac:Item>
    <cac:Price>
        <cbc:PriceAmount currencyID="JOD">5.000000000</cbc:PriceAmount>
        <cac:AllowanceCharge>
            <cbc:ChargeIndicator>false</cbc:ChargeIndicator>
            <cbc:AllowanceChargeReason>DISCOUNT</cbc:AllowanceChargeReason>
            <cbc:Amount currencyID="JOD">0.000000000</cbc:Amount>
        </cac:AllowanceCharge>
    </cac:Price>
</cac:InvoiceLine>
XML);

    expect($items->toXml())->toBe($expected);
});

test('it generates XML for multiple items', function () {
    $items = new InvoiceItems;

    // Add standard rate item
    $items->addItem('1')
        ->setQuantity(2)
        ->setUnitPrice(10.0)
        ->setDescription('Item 1')
        ->setTaxCategory('S', 16);

    // Add exempt item
    $items->addItem('2')
        ->setQuantity(1)
        ->setUnitPrice(5.0)
        ->setDescription('Item 2')
        ->setTaxCategory('Z');

    $xml = $items->toXml();

    expect($xml)
        ->toContain('<cac:InvoiceLine>')
        ->toContain('<cbc:ID>1</cbc:ID>')
        ->toContain('<cbc:ID>2</cbc:ID>')
        ->toContain('<cbc:Name>Item 1</cbc:Name>')
        ->toContain('<cbc:Name>Item 2</cbc:Name>');
});

test('it throws exception when required fields are missing', function () {
    $items = new InvoiceItems;

    $item = $items->addItem('1');

    expect(fn () => $items->toXml())
        ->toThrow(InvalidArgumentException::class, 'Quantity is required');

    $item->setQuantity(1);
    expect(fn () => $items->toXml())
        ->toThrow(InvalidArgumentException::class, 'Unit price is required');

    $item->setUnitPrice(10);
    expect(fn () => $items->toXml())
        ->toThrow(InvalidArgumentException::class, 'Description is required');
});

test('it throws exception when no items are added', function () {
    $items = new InvoiceItems;

    expect(fn () => $items->toXml())
        ->toThrow(InvalidArgumentException::class, 'At least one invoice item is required');
});

test('it properly escapes special characters in description', function () {
    $items = new InvoiceItems;
    $items->addItem('1')
        ->setQuantity(1)
        ->setUnitPrice(10)
        ->setDescription('Item & Special < > " \' Characters');

    $xml = $items->toXml();

    expect($xml)->toContain('<cbc:Name>Item &amp; Special &lt; &gt; &quot; &apos; Characters</cbc:Name>');
});

test('it formats numbers with two three decimal places', function () {
    $items = new InvoiceItems;
    $items->addItem('1')
        ->setQuantity(1.5)
        ->setUnitPrice(10.123456789)
        ->setDescription('Test')
        ->setDiscount(2.789456789);

    $xml = $items->toXml();

    expect($xml)
        ->toContain('<cbc:InvoicedQuantity unitCode="PCE">1.500000000</cbc:InvoicedQuantity>')
        ->toContain('<cbc:PriceAmount currencyID="JOD">10.123456789</cbc:PriceAmount>')
        ->toContain('<cbc:Amount currencyID="JOD">2.789456789</cbc:Amount>');
});

test('it calculates tax and totals correctly', function () {
    $items = new InvoiceItems;
    $items->addItem('1')
        ->setQuantity(2)
        ->setUnitPrice(100)
        ->setDescription('Test')
        ->setDiscount(20)
        ->setTaxCategory('S', 16);

    $xml = $items->toXml();

    // Line extension = (2 * 100) - 20 = 180
    // Tax amount = 180 * 0.16 = 28.8
    // Total = 180 + 28.8 = 208.8

    expect($xml)
        ->toContain('<cbc:LineExtensionAmount currencyID="JOD">180.000000000</cbc:LineExtensionAmount>')
        ->toContain('<cbc:TaxAmount currencyID="JOD">28.800000000</cbc:TaxAmount>')
        ->toContain('<cbc:RoundingAmount currencyID="JOD">208.800000000</cbc:RoundingAmount>');
});

test('it generates XML for standard rate item with tax and discount', function () {
    $items = new InvoiceItems;
    $items->addItem('1')
        ->setQuantity(33)
        ->setUnitPrice(2.0)
        ->setDescription('Biscuit')
        ->setDiscount(2.0)
        ->tax(7);

    $expected = $this->normalizeXml(<<<'XML'
<cac:InvoiceLine>
    <cbc:ID>1</cbc:ID>
    <cbc:InvoicedQuantity unitCode="PCE">33.000000000</cbc:InvoicedQuantity>
    <cbc:LineExtensionAmount currencyID="JOD">64.000000000</cbc:LineExtensionAmount>
    <cac:TaxTotal>
        <cbc:TaxAmount currencyID="JOD">4.480000000</cbc:TaxAmount>
        <cbc:RoundingAmount currencyID="JOD">68.480000000</cbc:RoundingAmount>
        <cac:TaxSubtotal>
            <cbc:TaxAmount currencyID="JOD">4.480000000</cbc:TaxAmount>
            <cac:TaxCategory>
                <cbc:ID schemeAgencyID="6" schemeID="UN/ECE 5305">S</cbc:ID>
                <cbc:Percent>7.000000000</cbc:Percent>
                <cac:TaxScheme>
                    <cbc:ID schemeAgencyID="6" schemeID="UN/ECE 5153">VAT</cbc:ID>
                </cac:TaxScheme>
            </cac:TaxCategory>
        </cac:TaxSubtotal>
    </cac:TaxTotal>
    <cac:Item>
        <cbc:Name>Biscuit</cbc:Name>
    </cac:Item>
    <cac:Price>
        <cbc:PriceAmount currencyID="JOD">2.000000000</cbc:PriceAmount>
        <cac:AllowanceCharge>
            <cbc:ChargeIndicator>false</cbc:ChargeIndicator>
            <cbc:AllowanceChargeReason>DISCOUNT</cbc:AllowanceChargeReason>
            <cbc:Amount currencyID="JOD">2.000000000</cbc:Amount>
        </cac:AllowanceCharge>
    </cac:Price>
</cac:InvoiceLine>
XML);

    expect($items->toXml())->toBe($expected);
});

test('it generates XML for standard rate item with tax but no discount', function () {
    $items = new InvoiceItems;
    $items->addItem('1')
        ->setQuantity(2)
        ->setUnitPrice(100)
        ->setDescription('Item')
        ->tax(16);

    $xml = $items->toXml();

    // Tax exclusive = 2 * 100 = 200
    // Tax amount = 200 * 0.16 = 32
    // Tax inclusive = 200 + 32 = 232
    expect($xml)
        ->toContain('<cbc:LineExtensionAmount currencyID="JOD">200.000000000</cbc:LineExtensionAmount>')
        ->toContain('<cbc:TaxAmount currencyID="JOD">32.000000000</cbc:TaxAmount>')
        ->toContain('<cbc:RoundingAmount currencyID="JOD">232.000000000</cbc:RoundingAmount>')
        ->toContain('<cbc:Amount currencyID="JOD">0.000000000</cbc:Amount>'); // Zero discount
});

test('it generates XML for tax exempted item with discount', function () {
    $items = new InvoiceItems;
    $items->addItem('1')
        ->setQuantity(2)
        ->setUnitPrice(100)
        ->setDescription('Item')
        ->setDiscount(50)
        ->taxExempted();

    $xml = $items->toXml();

    // Tax exclusive = (2 * 100) - 50 = 150
    // Tax amount = 0 (exempted)
    // Tax inclusive = 150
    expect($xml)
        ->toContain('<cbc:LineExtensionAmount currencyID="JOD">150.000000000</cbc:LineExtensionAmount>')
        ->toContain('<cbc:TaxAmount currencyID="JOD">0.000000000</cbc:TaxAmount>')
        ->toContain('<cbc:RoundingAmount currencyID="JOD">150.000000000</cbc:RoundingAmount>')
        ->toContain('<cbc:ID schemeAgencyID="6" schemeID="UN/ECE 5305">Z</cbc:ID>')
        ->toContain('<cbc:Percent>0.000000000</cbc:Percent>')
        ->toContain('<cbc:Amount currencyID="JOD">50.000000000</cbc:Amount>'); // Discount amount
});

test('it generates XML for tax exempted item without discount', function () {
    $items = new InvoiceItems;
    $items->addItem('1')
        ->setQuantity(2)
        ->setUnitPrice(100)
        ->setDescription('Item')
        ->taxExempted();

    $xml = $items->toXml();

    // Tax exclusive = 2 * 100 = 200
    // Tax amount = 0 (exempted)
    // Tax inclusive = 200
    expect($xml)
        ->toContain('<cbc:LineExtensionAmount currencyID="JOD">200.000000000</cbc:LineExtensionAmount>')
        ->toContain('<cbc:TaxAmount currencyID="JOD">0.000000000</cbc:TaxAmount>')
        ->toContain('<cbc:RoundingAmount currencyID="JOD">200.000000000</cbc:RoundingAmount>')
        ->toContain('<cbc:ID schemeAgencyID="6" schemeID="UN/ECE 5305">Z</cbc:ID>')
        ->toContain('<cbc:Percent>0.000000000</cbc:Percent>')
        ->toContain('<cbc:Amount currencyID="JOD">0.000000000</cbc:Amount>'); // Zero discount
});

test('it generates XML for zero rated item with discount', function () {
    $items = new InvoiceItems;
    $items->addItem('1')
        ->setQuantity(2)
        ->setUnitPrice(100)
        ->setDescription('Item')
        ->setDiscount(50)
        ->zeroTax();

    $xml = $items->toXml();

    // Tax exclusive = (2 * 100) - 50 = 150
    // Tax amount = 0 (zero rated)
    // Tax inclusive = 150
    expect($xml)
        ->toContain('<cbc:LineExtensionAmount currencyID="JOD">150.000000000</cbc:LineExtensionAmount>')
        ->toContain('<cbc:TaxAmount currencyID="JOD">0.000000000</cbc:TaxAmount>')
        ->toContain('<cbc:RoundingAmount currencyID="JOD">150.000000000</cbc:RoundingAmount>')
        ->toContain('<cbc:ID schemeAgencyID="6" schemeID="UN/ECE 5305">O</cbc:ID>')
        ->toContain('<cbc:Percent>0.000000000</cbc:Percent>')
        ->toContain('<cbc:Amount currencyID="JOD">50.000000000</cbc:Amount>'); // Discount amount
});

test('it generates XML for zero rated item without discount', function () {
    $items = new InvoiceItems;
    $items->addItem('1')
        ->setQuantity(2)
        ->setUnitPrice(100)
        ->setDescription('Item')
        ->zeroTax();

    $xml = $items->toXml();

    // Tax exclusive = 2 * 100 = 200
    // Tax amount = 0 (zero rated)
    // Tax inclusive = 200
    expect($xml)
        ->toContain('<cbc:LineExtensionAmount currencyID="JOD">200.000000000</cbc:LineExtensionAmount>')
        ->toContain('<cbc:TaxAmount currencyID="JOD">0.000000000</cbc:TaxAmount>')
        ->toContain('<cbc:RoundingAmount currencyID="JOD">200.000000000</cbc:RoundingAmount>')
        ->toContain('<cbc:ID schemeAgencyID="6" schemeID="UN/ECE 5305">O</cbc:ID>')
        ->toContain('<cbc:Percent>0.000000000</cbc:Percent>')
        ->toContain('<cbc:Amount currencyID="JOD">0.000000000</cbc:Amount>'); // Zero discount
});

test('it handles multiple items with different tax and discount combinations', function () {
    $items = new InvoiceItems;

    // Standard rate with tax and discount
    $items->addItem('1')
        ->setQuantity(2)
        ->setUnitPrice(100)
        ->setDescription('Item 1')
        ->setDiscount(50)
        ->tax(16);

    // Exempted with discount
    $items->addItem('2')
        ->setQuantity(3)
        ->setUnitPrice(50)
        ->setDescription('Item 2')
        ->setDiscount(25)
        ->taxExempted();

    // Zero rated without discount
    $items->addItem('3')
        ->setQuantity(1)
        ->setUnitPrice(200)
        ->setDescription('Item 3')
        ->zeroTax();

    $xml = $items->toXml();

    // First item
    // Tax exclusive = (2 * 100) - 50 = 150
    // Tax amount = 150 * 0.16 = 24
    // Tax inclusive = 150 + 24 = 174
    expect($xml)
        ->toContain('<cbc:LineExtensionAmount currencyID="JOD">150.000000000</cbc:LineExtensionAmount>')
        ->toContain('<cbc:TaxAmount currencyID="JOD">24.000000000</cbc:TaxAmount>')
        ->toContain('<cbc:RoundingAmount currencyID="JOD">174.000000000</cbc:RoundingAmount>')
        ->and($xml)
        ->toContain('<cbc:LineExtensionAmount currencyID="JOD">125.000000000</cbc:LineExtensionAmount>')
        ->toContain('<cbc:TaxAmount currencyID="JOD">0.000000000</cbc:TaxAmount>')
        ->toContain('<cbc:RoundingAmount currencyID="JOD">125.000000000</cbc:RoundingAmount>')
        ->and($xml)
        ->toContain('<cbc:LineExtensionAmount currencyID="JOD">200.000000000</cbc:LineExtensionAmount>')
        ->toContain('<cbc:TaxAmount currencyID="JOD">0.000000000</cbc:TaxAmount>')
        ->toContain('<cbc:RoundingAmount currencyID="JOD">200.000000000</cbc:RoundingAmount>');
});
