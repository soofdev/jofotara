<?php

use JBadarneh\JoFotara\JoFotaraService;

test('it throws exception when constructed with empty credentials', function () {
    expect(fn () => new JoFotaraService('', 'secret'))->toThrow(
        InvalidArgumentException::class,
        'JoFotara client ID and secret are required'
    )
        ->and(fn () => new JoFotaraService('client', ''))->toThrow(
            InvalidArgumentException::class,
            'JoFotara client ID and secret are required'
        );

});

test('it encodes invoice XML to base64', function () {
    $invoice = new JoFotaraService('test-client-id', 'test-client-secret');

    // Set up a basic invoice
    $invoice->basicInformation()
        ->setInvoiceId('INV-001')
        ->setUuid('123e4567-e89b-12d3-a456-426614174000')
        ->setIssueDate('16-02-2025')
        ->cash();

    $invoice->sellerInformation()
        ->setTin('12345678')
        ->setName('Test Seller');

    // Customer information
    $invoice->customerInformation()
        ->setId('123456789', 'TIN')
        ->setTin('123456789')
        ->setName('Test Buyer')
        ->setCityCode('JO-AM')
        ->setPhone('0791234567');

    $invoice->supplierIncomeSource('123456789');

    $invoice->items()
        ->addItem('1')
        ->setQuantity(1)
        ->setUnitPrice(100.0)
        ->setDescription('Test Item')
        ->tax(16);

    $invoice->invoiceTotals();

    $encodedInvoice = $invoice->encodeInvoice();

    $decodedInvoice = base64_decode($encodedInvoice);
    // Verify it's valid base64
    expect(base64_decode($encodedInvoice, true))->not->toBeNull()
        // Verify the decoded content contains our invoice data
        ->and($decodedInvoice)->toContain('INV-001')
        ->and($decodedInvoice)->toContain('123e4567-e89b-12d3-a456-426614174000')
        ->and($decodedInvoice)->toContain('2025-02-16')
        ->and($decodedInvoice)->toContain('100.0')
        ->and($decodedInvoice)->toContain('16.0')
        ->and($decodedInvoice)->toContain('116.0')
        ->and($decodedInvoice)->toContain('Test Item');
});

// TODO: Implement Mocked API tests
