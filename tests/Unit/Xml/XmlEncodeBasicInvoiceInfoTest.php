<?php

use JBadarneh\JoFotara\Sections\BasicInvoiceInformation;
use JBadarneh\JoFotara\Traits\XmlHelperTrait;

uses(XmlHelperTrait::class);

test('it generates exact XML structure for general sales invoice', function () {
    $invoice = new BasicInvoiceInformation;
    $invoice->setInvoiceId('INV001')
        ->setUuid('123e4567-e89b-12d3-a456-426614174000')
        ->setIssueDate('16-02-2025')
        ->setInvoiceType('general_sales')
        ->setPaymentMethod('012')
        ->setNote('Test invoice')
        ->setInvoiceCounter(1);

    $expected = $this->normalizeXml(<<<'XML'
<cbc:ID>INV001</cbc:ID>
<cbc:UUID>123e4567-e89b-12d3-a456-426614174000</cbc:UUID>
<cbc:IssueDate>2025-02-16</cbc:IssueDate>
<cbc:InvoiceTypeCode name="012">388</cbc:InvoiceTypeCode>
<cbc:Note>Test invoice</cbc:Note>
<cbc:DocumentCurrencyCode>JOD</cbc:DocumentCurrencyCode>
<cbc:TaxCurrencyCode>JOD</cbc:TaxCurrencyCode>
<cac:AdditionalDocumentReference>
    <cbc:ID>ICV</cbc:ID>
    <cbc:UUID>1</cbc:UUID>
</cac:AdditionalDocumentReference>
XML);

    expect($invoice->toXml())->toBe($expected);
});

test('it generates valid XML with all required fields', function () {
    $invoice = new BasicInvoiceInformation;
    $invoice->setInvoiceId('INV001')
        ->setUuid('123e4567-e89b-12d3-a456-426614174000')
        ->setIssueDate('16-02-2025')
        ->setInvoiceType('general_sales')
        ->setPaymentMethod('012')
        ->setNote('Test invoice')
        ->setInvoiceCounter(1);

    $xml = $invoice->toXml();

    // Test presence of required elements
    expect($xml)->toContain('<cbc:ID>INV001</cbc:ID>')
        ->toContain('<cbc:UUID>123e4567-e89b-12d3-a456-426614174000</cbc:UUID>')
        ->toContain('<cbc:IssueDate>2025-02-16</cbc:IssueDate>')
        ->toContain('<cbc:InvoiceTypeCode name="012">388</cbc:InvoiceTypeCode>')
        ->toContain('<cbc:Note>Test invoice</cbc:Note>')
        ->toContain('<cbc:DocumentCurrencyCode>JOD</cbc:DocumentCurrencyCode>')
        ->toContain('<cbc:TaxCurrencyCode>JOD</cbc:TaxCurrencyCode>')
        ->toContain('<cac:AdditionalDocumentReference>')
        ->toContain('<cbc:ID>ICV</cbc:ID>')
        ->toContain('<cbc:UUID>1</cbc:UUID>');
});

test('it defaults to JOD currency', function () {
    $invoice = new BasicInvoiceInformation;
    $invoice->setInvoiceId('INV001')
        ->setUuid('123e4567-e89b-12d3-a456-426614174000')
        ->setIssueDate('16-02-2025')
        ->setInvoiceType('income')
        ->cash();

    $xml = $invoice->toXml();

    expect($xml)->toContain('<cbc:DocumentCurrencyCode>JOD</cbc:DocumentCurrencyCode>')
        ->toContain('<cbc:TaxCurrencyCode>JOD</cbc:TaxCurrencyCode>');
});

test('it generates valid XML without optional note', function () {
    $invoice = new BasicInvoiceInformation;
    $invoice->setInvoiceId('INV001')
        ->setUuid('123e4567-e89b-12d3-a456-426614174000')
        ->setIssueDate('16-02-2025')
        ->setInvoiceType('income')
        ->cash();

    $xml = $invoice->toXml();

    expect($xml)->not->toContain('<cbc:Note>');
});

test('it throws exception when invoice ID is missing', function () {
    $invoice = new BasicInvoiceInformation;
    $invoice->setUuid('123e4567-e89b-12d3-a456-426614174000')
        ->setIssueDate('16-02-2025');

    expect(fn () => $invoice->toXml())
        ->toThrow(InvalidArgumentException::class, 'Invoice ID is required');
});

test('it throws exception when UUID is missing', function () {
    $invoice = new BasicInvoiceInformation;
    $invoice->setInvoiceId('INV001')
        ->setIssueDate('16-02-2025');

    expect(fn () => $invoice->toXml())
        ->toThrow(InvalidArgumentException::class, 'UUID is required');
});

test('it throws exception when issue date is missing', function () {
    $invoice = new BasicInvoiceInformation;
    $invoice->setInvoiceId('INV001')
        ->setUuid('123e4567-e89b-12d3-a456-426614174000');

    expect(fn () => $invoice->toXml())
        ->toThrow(InvalidArgumentException::class, 'Issue date is required');
});

test('it formats date correctly in XML', function () {
    $invoice = new BasicInvoiceInformation;
    $invoice->setInvoiceId('INV001')
        ->setUuid('123e4567-e89b-12d3-a456-426614174000')
        ->setIssueDate('16-02-2025')
        ->setInvoiceType('income')
        ->cash();

    $xml = $invoice->toXml();

    expect($xml)->toContain('<cbc:IssueDate>2025-02-16</cbc:IssueDate>');
});

test('it properly handles Arabic text in note', function () {
    $invoice = new BasicInvoiceInformation;
    $invoice->setInvoiceId('INV001')
        ->setUuid('123e4567-e89b-12d3-a456-426614174000')
        ->setIssueDate('16-02-2025')
        ->setInvoiceType('income')
        ->cash()
        ->setNote('ملاحظة على الفاتورة');

    $xml = $invoice->toXml();

    expect($xml)->toContain('<cbc:Note>ملاحظة على الفاتورة</cbc:Note>');
});

test('it escapes special characters in XML', function () {
    $invoice = new BasicInvoiceInformation;
    $invoice->setInvoiceId('INV001')
        ->setUuid('123e4567-e89b-12d3-a456-426614174000')
        ->setIssueDate('16-02-2025')
        ->setInvoiceType('income')
        ->cash()
        ->setNote('Note with special chars: < > & " \'');

    $xml = $invoice->toXml();

    expect($xml)->toContain('<cbc:Note>Note with special chars: &lt; &gt; &amp; &quot; &apos;</cbc:Note>');
});

test('it handles DateTime object for issue date', function () {
    $invoice = new BasicInvoiceInformation;
    $date = new DateTime('2025-02-16');

    $invoice->setInvoiceId('INV001')
        ->setUuid('123e4567-e89b-12d3-a456-426614174000')
        ->setIssueDate($date)
        ->setInvoiceType('income')
        ->cash();

    $xml = $invoice->toXml();

    expect($xml)->toContain('<cbc:IssueDate>2025-02-16</cbc:IssueDate>');
});

test('it generates XML for income invoice with cash payment', function () {
    $invoice = new BasicInvoiceInformation;
    $invoice->setInvoiceId('INV001')
        ->setUuid('123e4567-e89b-12d3-a456-426614174000')
        ->setIssueDate('16-02-2025')
        ->setInvoiceType('income')
        ->cash();

    $xml = $invoice->toXml();

    expect($xml)->toContain('<cbc:InvoiceTypeCode name="011">388</cbc:InvoiceTypeCode>');
});

test('it generates XML for income invoice with receivable payment', function () {
    $invoice = new BasicInvoiceInformation;
    $invoice->setInvoiceId('INV001')
        ->setUuid('123e4567-e89b-12d3-a456-426614174000')
        ->setIssueDate('16-02-2025')
        ->setInvoiceType('income')
        ->receivable();

    $xml = $invoice->toXml();

    expect($xml)->toContain('<cbc:InvoiceTypeCode name="021">388</cbc:InvoiceTypeCode>');
});

test('it generates XML for general sales invoice with cash payment', function () {
    $invoice = new BasicInvoiceInformation;
    $invoice->setInvoiceId('INV001')
        ->setUuid('123e4567-e89b-12d3-a456-426614174000')
        ->setIssueDate('16-02-2025')
        ->setInvoiceType('general_sales')
        ->cash();

    $xml = $invoice->toXml();

    expect($xml)->toContain('<cbc:InvoiceTypeCode name="012">388</cbc:InvoiceTypeCode>');
});

test('it generates XML for general sales invoice with receivable payment', function () {
    $invoice = new BasicInvoiceInformation;
    $invoice->setInvoiceId('INV001')
        ->setUuid('123e4567-e89b-12d3-a456-426614174000')
        ->setIssueDate('16-02-2025')
        ->setInvoiceType('general_sales')
        ->receivable();

    $xml = $invoice->toXml();

    expect($xml)->toContain('<cbc:InvoiceTypeCode name="022">388</cbc:InvoiceTypeCode>');
});

test('it generates XML for special sales invoice with cash payment', function () {
    $invoice = new BasicInvoiceInformation;
    $invoice->setInvoiceId('INV001')
        ->setUuid('123e4567-e89b-12d3-a456-426614174000')
        ->setIssueDate('16-02-2025')
        ->setInvoiceType('special_sales')
        ->cash();

    $xml = $invoice->toXml();

    expect($xml)->toContain('<cbc:InvoiceTypeCode name="013">388</cbc:InvoiceTypeCode>');
});

test('it generates XML for special sales invoice with receivable payment', function () {
    $invoice = new BasicInvoiceInformation;
    $invoice->setInvoiceId('INV001')
        ->setUuid('123e4567-e89b-12d3-a456-426614174000')
        ->setIssueDate('16-02-2025')
        ->setInvoiceType('special_sales')
        ->receivable();

    $xml = $invoice->toXml();

    expect($xml)->toContain('<cbc:InvoiceTypeCode name="023">388</cbc:InvoiceTypeCode>');
});

test('it throws exception when invoice type is not set', function () {
    $invoice = new BasicInvoiceInformation;
    $invoice->setInvoiceId('INV001')
        ->setUuid('123e4567-e89b-12d3-a456-426614174000')
        ->setIssueDate('16-02-2025');

    expect(fn () => $invoice->cash())
        ->toThrow(InvalidArgumentException::class, 'Invoice type must be set before setting payment method. Use setInvoiceType() first.');

    // Should also throw when trying to validate
    expect(fn () => $invoice->validateSection())
        ->toThrow(InvalidArgumentException::class);
});
