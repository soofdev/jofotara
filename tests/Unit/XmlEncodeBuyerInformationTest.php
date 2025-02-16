<?php

use JBadarneh\JoFotara\Sections\BuyerInformation;

test('it generates exact XML structure', function () {
    $buyer = new BuyerInformation();
    $buyer->setId('123456789', 'NIN')
          ->setPostalCode('11937')
          ->setCityCode('JO-AM')
          ->setName('John Doe')
          ->setPhone('0791234567')
          ->setTin('987654321');
    
    $expected = <<<XML
<cac:AccountingCustomerParty>
    <cac:Party>
        <cac:PartyIdentification>
            <cbc:ID schemeID="NIN">123456789</cbc:ID>
        </cac:PartyIdentification>
        <cac:PostalAddress>
            <cbc:PostalZone>11937</cbc:PostalZone>
            <cbc:CountrySubentityCode>JO-AM</cbc:CountrySubentityCode>
            <cac:Country>
                <cbc:IdentificationCode>JO</cbc:IdentificationCode>
            </cac:Country>
        </cac:PostalAddress>
        <cac:PartyTaxScheme>
            <cbc:CompanyID>987654321</cbc:CompanyID>
            <cac:TaxScheme>
                <cbc:ID>VAT</cbc:ID>
            </cac:TaxScheme>
        </cac:PartyTaxScheme>
        <cac:PartyLegalEntity>
            <cbc:RegistrationName>John Doe</cbc:RegistrationName>
        </cac:PartyLegalEntity>
    </cac:Party>
    <cac:AccountingContact>
        <cbc:Telephone>0791234567</cbc:Telephone>
    </cac:AccountingContact>
</cac:AccountingCustomerParty>
XML;

    expect($buyer->toXml())->toBe($expected);
});

test('it generates valid XML with minimal fields', function () {
    $buyer = new BuyerInformation();
    $buyer->setId('123456789', 'NIN');
    
    $expected = <<<XML
<cac:AccountingCustomerParty>
    <cac:Party>
        <cac:PartyIdentification>
            <cbc:ID schemeID="NIN">123456789</cbc:ID>
        </cac:PartyIdentification>
    </cac:Party>
</cac:AccountingCustomerParty>
XML;

    expect($buyer->toXml())->toBe($expected);
});

test('it escapes special characters in XML', function () {
    $buyer = new BuyerInformation();
    $buyer->setId('123456789', 'NIN')
          ->setName('John & Sons Trading LLC')
          ->setPostalCode('11937 < 12000');
    
    $expected = <<<XML
<cac:AccountingCustomerParty>
    <cac:Party>
        <cac:PartyIdentification>
            <cbc:ID schemeID="NIN">123456789</cbc:ID>
        </cac:PartyIdentification>
        <cac:PostalAddress>
            <cbc:PostalZone>11937 &lt; 12000</cbc:PostalZone>
            <cac:Country>
                <cbc:IdentificationCode>JO</cbc:IdentificationCode>
            </cac:Country>
        </cac:PostalAddress>
        <cac:PartyLegalEntity>
            <cbc:RegistrationName>John &amp; Sons Trading LLC</cbc:RegistrationName>
        </cac:PartyLegalEntity>
    </cac:Party>
</cac:AccountingCustomerParty>
XML;

    expect($buyer->toXml())->toBe($expected);
});
