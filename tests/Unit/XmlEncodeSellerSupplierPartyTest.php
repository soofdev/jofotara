<?php

use JBadarneh\JoFotara\Sections\SellerSupplierParty;

test('it requires sequence of income source', function () {
    $supplier = new SellerSupplierParty();
    
    expect(fn() => $supplier->toXml())->toThrow(
        InvalidArgumentException::class,
        'Sequence of income source is required'
    );
});

test('it generates exact XML structure', function () {
    $supplier = new SellerSupplierParty();
    $supplier->setSequenceId('9932895');
    
    $expected = <<<XML
<cac:SellerSupplierParty>
    <cac:Party>
        <cac:PartyIdentification>
            <cbc:ID>9932895</cbc:ID>
        </cac:PartyIdentification>
    </cac:Party>
</cac:SellerSupplierParty>
XML;

    expect($supplier->toXml())->toBe($expected);
});

test('it escapes special characters in XML', function () {
    $supplier = new SellerSupplierParty();
    $supplier->setSequenceId('9932895 & 9932896');
    
    $expected = <<<XML
<cac:SellerSupplierParty>
    <cac:Party>
        <cac:PartyIdentification>
            <cbc:ID>9932895 &amp; 9932896</cbc:ID>
        </cac:PartyIdentification>
    </cac:Party>
</cac:SellerSupplierParty>
XML;

    expect($supplier->toXml())->toBe($expected);
});
