<?php

use JBadarneh\JoFotara\Sections\BasicInvoiceInformation;


test('it generates valid XML with all required fields', function () {
    $invoice = new BasicInvoiceInformation();
    $invoice->setInvoiceId('INV001')
            ->setUuid('123e4567-e89b-12d3-a456-426614174000')
            ->setIssueDate('16-02-2025')
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
    $invoice = new BasicInvoiceInformation();
    $invoice->setInvoiceId('INV001')
            ->setUuid('123e4567-e89b-12d3-a456-426614174000')
            ->setIssueDate('16-02-2025');
    
    $xml = $invoice->toXml();
    
    expect($xml)->toContain('<cbc:DocumentCurrencyCode>JOD</cbc:DocumentCurrencyCode>')
                ->toContain('<cbc:TaxCurrencyCode>JOD</cbc:TaxCurrencyCode>');
});

test('it generates valid XML without optional note', function () {
    $invoice = new BasicInvoiceInformation();
    $invoice->setInvoiceId('INV001')
            ->setUuid('123e4567-e89b-12d3-a456-426614174000')
            ->setIssueDate('16-02-2025');
    
    $xml = $invoice->toXml();
    
    expect($xml)->not->toContain('<cbc:Note>');
});

test('it throws exception when invoice ID is missing', function () {
    $invoice = new BasicInvoiceInformation();
    $invoice->setUuid('123e4567-e89b-12d3-a456-426614174000')
            ->setIssueDate('16-02-2025');
    
    expect(fn() => $invoice->toXml())
        ->toThrow(InvalidArgumentException::class, 'Invoice ID is required');
});

test('it throws exception when UUID is missing', function () {
    $invoice = new BasicInvoiceInformation();
    $invoice->setInvoiceId('INV001')
            ->setIssueDate('16-02-2025');
    
    expect(fn() => $invoice->toXml())
        ->toThrow(InvalidArgumentException::class, 'UUID is required');
});

test('it throws exception when issue date is missing', function () {
    $invoice = new BasicInvoiceInformation();
    $invoice->setInvoiceId('INV001')
            ->setUuid('123e4567-e89b-12d3-a456-426614174000');
    
    expect(fn() => $invoice->toXml())
        ->toThrow(InvalidArgumentException::class, 'Issue date is required');
});

test('it formats date correctly in XML', function () {
    $invoice = new BasicInvoiceInformation();
    $invoice->setInvoiceId('INV001')
            ->setUuid('123e4567-e89b-12d3-a456-426614174000')
            ->setIssueDate('16-02-2025');
    
    $xml = $invoice->toXml();
    
    expect($xml)->toContain('<cbc:IssueDate>2025-02-16</cbc:IssueDate>');
});

test('it properly handles Arabic text in note', function () {
    $invoice = new BasicInvoiceInformation();
    $invoice->setInvoiceId('INV001')
            ->setUuid('123e4567-e89b-12d3-a456-426614174000')
            ->setIssueDate('16-02-2025')
            ->setNote('ملاحظة على الفاتورة');
    
    $xml = $invoice->toXml();
    
    expect($xml)->toContain('<cbc:Note>ملاحظة على الفاتورة</cbc:Note>');
});

test('it escapes special characters in XML', function () {
    $invoice = new BasicInvoiceInformation();
    $invoice->setInvoiceId('INV001')
            ->setUuid('123e4567-e89b-12d3-a456-426614174000')
            ->setIssueDate('16-02-2025')
            ->setNote('Note with special chars: < > & " \'');
    
    $xml = $invoice->toXml();
    
    expect($xml)->toContain('<cbc:Note>Note with special chars: &lt; &gt; &amp; &quot; &apos;</cbc:Note>');
});

test('it handles DateTime object for issue date', function () {
    $invoice = new BasicInvoiceInformation();
    $date = new DateTime('2025-02-16');
    
    $invoice->setInvoiceId('INV001')
            ->setUuid('123e4567-e89b-12d3-a456-426614174000')
            ->setIssueDate($date);
    
    $xml = $invoice->toXml();
    
    expect($xml)->toContain('<cbc:IssueDate>2025-02-16</cbc:IssueDate>');
});