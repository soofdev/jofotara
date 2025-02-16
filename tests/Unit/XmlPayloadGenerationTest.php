<?php

use JBadarneh\JoFotara\JoFotaraClass;

test('generates valid XML for cash invoice with tax exempt item', function () {
    $invoice = new JoFotaraClass;

    // 1. Basic Information
    $invoice->basicInformation()
        ->setInvoiceId('INV-001')
        ->setUuid('123e4567-e89b-12d3-a456-426614174000')
        ->setIssueDate('16-02-2025')
        ->cash();

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
    $invoice->items()
        ->addItem('1')
        ->setQuantity(2)
        ->setUnitPrice(10.0)
        ->setDescription('Test Item')
        ->taxExempted();

    // 6. Invoice Totals (will be auto-calculated)
    $invoice->invoiceTotals();

    $expectedXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2.1" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2">
<cbc:UBLVersionID>2.1</cbc:UBLVersionID>
<cbc:ID>INV-001</cbc:ID>
<cbc:UUID>123e4567-e89b-12d3-a456-426614174000</cbc:UUID>
<cbc:IssueDate>2025-02-16</cbc:IssueDate>
<cbc:InvoiceTypeCode name="012">388</cbc:InvoiceTypeCode>
<cbc:DocumentCurrencyCode>JOD</cbc:DocumentCurrencyCode>
<cbc:TaxCurrencyCode>JOD</cbc:TaxCurrencyCode>
<cac:AdditionalDocumentReference>
    <cbc:ID>ICV</cbc:ID>
    <cbc:UUID>1</cbc:UUID>
</cac:AdditionalDocumentReference>
<cac:AccountingSupplierParty>
    <cac:Party>
        <cac:PostalAddress>
            <cac:Country>
                <cbc:IdentificationCode>JO</cbc:IdentificationCode>
            </cac:Country>
        </cac:PostalAddress>
        <cac:PartyTaxScheme>
            <cbc:CompanyID>123456789</cbc:CompanyID>
            <cac:TaxScheme>
                <cbc:ID>VAT</cbc:ID>
            </cac:TaxScheme>
        </cac:PartyTaxScheme>
        <cac:PartyLegalEntity>
            <cbc:RegistrationName>Seller Company</cbc:RegistrationName>
        </cac:PartyLegalEntity>
    </cac:Party>
</cac:AccountingSupplierParty>
<cac:AccountingCustomerParty>
    <cac:Party>
        <cac:PartyIdentification>
            <cbc:ID schemeID="TIN">987654321</cbc:ID>
        </cac:PartyIdentification>
        <cac:PostalAddress>
            <cbc:PostalZone>11937</cbc:PostalZone>
            <cbc:CountrySubentityCode>JO-IR</cbc:CountrySubentityCode>
            <cac:Country>
                <cbc:IdentificationCode>JO</cbc:IdentificationCode>
            </cac:Country>
        </cac:PostalAddress>
        <cac:PartyLegalEntity>
            <cbc:RegistrationName>Customer 123</cbc:RegistrationName>
        </cac:PartyLegalEntity>
    </cac:Party>
</cac:AccountingCustomerParty>
<cac:SellerSupplierParty>
    <cac:Party>
        <cac:PartyIdentification>
            <cbc:ID>1</cbc:ID>
        </cac:PartyIdentification>
    </cac:Party>
</cac:SellerSupplierParty>
<cac:TaxTotal>
    <cbc:TaxAmount currencyID="JOD">0.00</cbc:TaxAmount>
</cac:TaxTotal>
<cac:LegalMonetaryTotal>
    <cbc:TaxExclusiveAmount currencyID="JOD">20.00</cbc:TaxExclusiveAmount>
    <cbc:TaxInclusiveAmount currencyID="JOD">20.00</cbc:TaxInclusiveAmount>
    <cbc:PayableAmount currencyID="JOD">20.00</cbc:PayableAmount>
</cac:LegalMonetaryTotal>
<cac:InvoiceLine>
    <cbc:ID>1</cbc:ID>
    <cbc:InvoicedQuantity unitCode="PCE">2.00</cbc:InvoicedQuantity>
    <cbc:LineExtensionAmount currencyID="JOD">20.00</cbc:LineExtensionAmount>
    <cac:TaxTotal>
        <cbc:TaxAmount currencyID="JOD">0.00</cbc:TaxAmount>
        <cbc:RoundingAmount currencyID="JOD">20.00</cbc:RoundingAmount>
        <cac:TaxSubtotal>
            <cbc:TaxAmount currencyID="JOD">0.00</cbc:TaxAmount>
            <cac:TaxCategory>
                <cbc:ID schemeAgencyID="6" schemeID="UN/ECE 5305">Z</cbc:ID>
                <cbc:Percent>0.00</cbc:Percent>
                <cac:TaxScheme>
                    <cbc:ID schemeAgencyID="6" schemeID="UN/ECE 5153">VAT</cbc:ID>
                </cac:TaxScheme>
            </cac:TaxCategory>
        </cac:TaxSubtotal>
    </cac:TaxTotal>
    <cac:Item>
        <cbc:Name>Test Item</cbc:Name>
    </cac:Item>
    <cac:Price>
        <cbc:PriceAmount currencyID="JOD">10.00</cbc:PriceAmount>
        <cac:AllowanceCharge>
            <cbc:ChargeIndicator>false</cbc:ChargeIndicator>
            <cbc:AllowanceChargeReason>DISCOUNT</cbc:AllowanceChargeReason>
            <cbc:Amount currencyID="JOD">0.00</cbc:Amount>
        </cac:AllowanceCharge>
    </cac:Price>
</cac:InvoiceLine>
</Invoice>
XML;

    expect($invoice->generateXml())->toBe($expectedXml);
});
