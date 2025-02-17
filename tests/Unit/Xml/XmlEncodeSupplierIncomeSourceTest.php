<?php

use JBadarneh\JoFotara\Sections\SupplierIncomeSource;
use JBadarneh\JoFotara\Traits\XmlHelperTrait;

uses(XmlHelperTrait::class);

test('it generates exact XML structure', function () {
    $supplier = new SupplierIncomeSource('9932895');

    $expected = $this->normalizeXml(<<<'XML'
<cac:SellerSupplierParty>
    <cac:Party>
        <cac:PartyIdentification>
            <cbc:ID>9932895</cbc:ID>
        </cac:PartyIdentification>
    </cac:Party>
</cac:SellerSupplierParty>
XML);

    expect($supplier->toXml())->toBe($expected);
});
