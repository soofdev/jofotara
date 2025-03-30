<?php

use JBadarneh\JoFotara\Sections\ReasonForReturn;
use JBadarneh\JoFotara\Traits\XmlHelperTrait;

uses(XmlHelperTrait::class);

test('it generates exact XML structure for return reason', function () {
    $reasonForReturn = new ReasonForReturn();
    $reasonForReturn->setReason('Defective item returned');

    $expected = $this->normalizeXml(<<<'XML'
<cac:PaymentMeans><cbc:PaymentMeansCode listID="UN/ECE 4461">10</cbc:PaymentMeansCode><cbc:InstructionNote>Defective item returned</cbc:InstructionNote></cac:PaymentMeans>
XML);

    expect($reasonForReturn->toXml())->toBe($expected);
});
