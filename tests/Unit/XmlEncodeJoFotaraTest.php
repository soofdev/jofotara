<?php

use JBadarneh\JoFotara\JoFotaraClass;

test('it generates XML for invoice with single tax exempt item', function () {
    $invoice = new JoFotaraClass();

    // 1. Basic Information
    $invoice->basicInformation()
        ->setInvoiceId('INV-001')
        ->setUuid('123e4567-e89b-12d3-a456-426614174000')
        ->setIssueDate('16-02-2025')
        ->setPaymentMethod('012');

    // 2. Seller Information
    $invoice->sellerInformation()
        ->setName('Seller Company')
        ->setTin('123456789');

    // 3. Buyer Information
    $invoice->buyerInformation()
        ->setId('987654321', 'TIN')
        ->setName('Customer 123')
        ->setPostalCode('11937')
        ->setCityCode('JO-IR');

    // 4. Seller Supplier Party
    $invoice->supplierInformation()
        ->setSequenceId('1');

    // 5. Invoice Items
    $item = $invoice->items()
    ->addItem('1')
    ->setQuantity(2)
    ->setUnitPrice(10.0)
        ->setDescription('Test Item')
    ->taxExempted();

    // 6. Invoice Totals (will be auto-calculated)
    $invoice->invoiceTotals();

    $xml = $invoice->generateXml();

    // Verify XML structure contains all required sections in order
    expect($xml)
        ->toContain('<?xml version="1.0" encoding="UTF-8"?>')
        ->toContain('<Invoice>')
        // 1. Basic Information
        ->toContain('<cbc:ID>INV-001</cbc:ID>')
        ->toContain('<cbc:IssueDate>2025-02-16</cbc:IssueDate>')
        ->toContain('<cbc:InvoiceTypeCode name="012">388</cbc:InvoiceTypeCode>')
        // 2. Seller Information
        ->toContain('<cac:AccountingSupplierParty>')
        ->toContain('<cac:Party>')
        ->toContain('<cac:PartyTaxScheme>')
        ->toContain('<cbc:CompanyID>123456789</cbc:CompanyID>')
        // 3. Buyer Information
        ->toContain('<cac:AccountingCustomerParty>')
        ->toContain('<cbc:ID schemeID="TIN">987654321</cbc:ID>')
        ->toContain('<cbc:PostalZone>11937</cbc:PostalZone>')
        ->toContain('<cbc:CountrySubentityCode>JO-IR</cbc:CountrySubentityCode>')
        ->toContain('<cbc:IdentificationCode>JO</cbc:IdentificationCode>')
        // 4. Seller Supplier Party
        ->toContain('<cac:SellerSupplierParty>')
        ->toContain('<cac:PartyIdentification>')
        ->toContain('<cbc:ID>1</cbc:ID>')
        // 5. Invoice Items
        ->toContain('<cac:InvoiceLine>')
        ->toContain('<cbc:ID>1</cbc:ID>')
        ->toContain('<cbc:InvoicedQuantity unitCode="PCE">2.00</cbc:InvoicedQuantity>')
        ->toContain('<cbc:LineExtensionAmount currencyID="JOD">20.00</cbc:LineExtensionAmount>')
        ->toContain('<cbc:Name>Test Item</cbc:Name>')
        ->toContain('<cbc:ID schemeAgencyID="6" schemeID="UN/ECE 5305">Z</cbc:ID>')
        ->toContain('<cbc:Percent>0.00</cbc:Percent>')
        // 6. Invoice Totals
        ->toContain('<cac:LegalMonetaryTotal>')
        ->toContain('<cbc:TaxExclusiveAmount currencyID="JOD">20.00</cbc:TaxExclusiveAmount>')
        ->toContain('<cbc:TaxInclusiveAmount currencyID="JOD">20.00</cbc:TaxInclusiveAmount>')
        ->toContain('<cbc:Amount currencyID="JOD">0.00</cbc:Amount>')
        ->toContain('<cbc:TaxAmount currencyID="JOD">0.00</cbc:TaxAmount>')
        ->toContain('<cbc:PayableAmount currencyID="JOD">20.00</cbc:PayableAmount>')
        ->toContain('</Invoice>');
});
