<?php

use JBadarneh\JoFotara\Sections\ReasonForReturn;

test('it sets return reason correctly', function () {
    $reasonForReturn = new ReasonForReturn();
    
    $reasonForReturn->setReason('Defective item returned');

    expect($reasonForReturn->toArray()['reason'])->toBe('Defective item returned');
});

test('it validates reason is required', function () {
    $reasonForReturn = new ReasonForReturn();

    expect(fn () => $reasonForReturn->validateSection())
        ->toThrow(InvalidArgumentException::class, 'Return reason is required');
});
