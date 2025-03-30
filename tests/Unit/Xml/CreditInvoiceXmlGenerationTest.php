<?php

use JBadarneh\JoFotara\JoFotaraService;
use JBadarneh\JoFotara\Tests\Helpers\XmlSchemaValidator;
use JBadarneh\JoFotara\Traits\XmlHelperTrait;

uses(XmlSchemaValidator::class, XmlHelperTrait::class);

function setupCreditInvoice(): JoFotaraService
{
    $invoice = new JoFotaraService('test-client-id', 'test-client-secret');

    $invoice->basicInformation()
        ->setInvoiceId('CR-001')
        ->setUuid('123e4567-e89b-12d3-a456-426614174000')
        ->setIssueDate('16-02-2025')
        ->setInvoiceType('general_sales')
        ->asCreditInvoice('INV-001', 'original-uuid', 200.00)
        ->cash();

    return $invoice;
}

test('validates credit invoice requires return reason', function () {
    $invoice = setupCreditInvoice();

    $invoice->sellerInformation()
        ->setTin('12345678')
        ->setName('Test Seller');

    expect(fn () => $invoice->generateXml())
        ->toThrow(InvalidArgumentException::class, 'Credit invoices require a reason for return');
});

test('generates valid credit invoice XML', function () {
    $invoice = setupCreditInvoice();

    // Add billing reference
    // Add reason for return
    $invoice->setReasonForReturn('Defective item returned');

    // Add seller information
    $invoice->sellerInformation()
        ->setTin('12345678')
        ->setName('Test Seller');

    // Add supplier income source
    $invoice->supplierIncomeSource('1');

    // Add customer information
    $invoice->customerInformation()
        ->setId('987654321', 'TIN')
        ->setName('Test Customer');

    // Add items
    $invoice->items()
        ->addItem('1')
        ->setQuantity(1)
        ->setUnitPrice(100.0)
        ->setDescription('Test Item')
        ->tax(16);

    // Add totals
    $invoice->invoiceTotals()
        ->setTaxExclusiveAmount(100.00)
        ->setTaxInclusiveAmount(116.00)
        ->setTaxTotalAmount(16.00)
        ->setPayableAmount(116.00);

    $xml = $invoice->generateXml();

    // Test credit invoice specific elements
    expect($xml)
        ->toContain('<cbc:InvoiceTypeCode name="012">381</cbc:InvoiceTypeCode>')
        ->toContain('<cbc:ID>INV-001</cbc:ID>')
        ->toContain('<cbc:UUID>original-uuid</cbc:UUID>')
        ->toContain('<cbc:DocumentDescription>200.00</cbc:DocumentDescription>')
        ->toContain('<cbc:InstructionNote>Defective item returned</cbc:InstructionNote>');

    // Validate against schema
    $result = $this->validateAgainstUblSchema($this->normalizeXml($xml));
    expect($result['isValid'])->toBeTrue();
});
