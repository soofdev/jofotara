<?php

use JBadarneh\JoFotara\Sections\SellerInformation;
use JBadarneh\JoFotara\Traits\XmlHelperTrait;

uses(XmlHelperTrait::class);

test('it generates exact XML structure', function () {
    $seller = new SellerInformation;
    $seller->setTin('12345678')
        ->setName('Test Company');

    $expected = $this->normalizeXml(<<<'XML'
<cac:AccountingSupplierParty>
    <cac:Party>
        <cac:PostalAddress>
            <cac:Country>
                <cbc:IdentificationCode>JO</cbc:IdentificationCode>
            </cac:Country>
        </cac:PostalAddress>
        <cac:PartyTaxScheme>
            <cbc:CompanyID>12345678</cbc:CompanyID>
            <cac:TaxScheme>
                <cbc:ID>VAT</cbc:ID>
            </cac:TaxScheme>
        </cac:PartyTaxScheme>
        <cac:PartyLegalEntity>
            <cbc:RegistrationName>Test Company</cbc:RegistrationName>
        </cac:PartyLegalEntity>
    </cac:Party>
</cac:AccountingSupplierParty>
XML);

    expect($seller->toXml())->toBe($expected);
});

test('it generates valid seller XML', function () {
    $seller = new SellerInformation;
    $seller->setTin('12345678')
        ->setName('Test Company');

    $xml = $seller->toXml();

    expect($xml)
        ->toContain('<cac:AccountingSupplierParty>')
        ->toContain('<cac:Party>')
        ->toContain('<cac:PostalAddress>')
        ->toContain('<cac:Country>')
        ->toContain('<cbc:IdentificationCode>JO</cbc:IdentificationCode>')
        ->toContain('<cac:PartyTaxScheme>')
        ->toContain('<cbc:CompanyID>12345678</cbc:CompanyID>')
        ->toContain('<cac:TaxScheme>')
        ->toContain('<cbc:ID>VAT</cbc:ID>')
        ->toContain('<cac:PartyLegalEntity>')
        ->toContain('<cbc:RegistrationName>Test Company</cbc:RegistrationName>');
});

test('it properly formats XML with indentation', function () {
    $seller = new SellerInformation;
    $seller->setTin('12345678')
        ->setName('Test Company');

    $xml = $seller->toXml();
    $lines = explode("\n", $xml);

    // Check indentation of nested elements
    expect($lines)->toHaveCount(18) // Including opening and closing tags
        ->and($lines[1])->toStartWith('    <cac:Party>')
        ->and($lines[2])->toStartWith('        <cac:PostalAddress>')
        ->and($lines[3])->toStartWith('            <cac:Country>');
});

test('it handles special characters in XML', function () {
    $seller = new SellerInformation;
    $seller->setTin('12345678')
        ->setName('Company & Trading Co.');

    $xml = $seller->toXml();

    expect($xml)
        ->toContain('<cbc:RegistrationName>Company &amp; Trading Co.</cbc:RegistrationName>');
});

test('it handles Arabic text in XML', function () {
    $seller = new SellerInformation;
    $seller->setTin('12345678')
        ->setName('شركة الاختبار للتجارة');

    $xml = $seller->toXml();

    expect($xml)
        ->toContain('<cbc:RegistrationName>شركة الاختبار للتجارة</cbc:RegistrationName>');
});
