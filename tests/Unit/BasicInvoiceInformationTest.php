<?php

use JBadarneh\JoFotara\JoFotaraService;

test('it can set basic invoice information', function () {
    $invoice = new JoFotaraService('test-client-id', 'test-client-secret');

    $invoice->basicInformation()
        ->setInvoiceId('INV001')
        ->setUuid('123e4567-e89b-12d3-a456-426614174000')
        ->setIssueDate('16-02-2025')
        ->setPaymentMethod('012')
        ->setNote('Test invoice')
        ->setInvoiceCounter(1);

    $data = $invoice->basicInformation()->toArray();

    expect($data['invoiceId'])->toBe('INV001')
        ->and($data['uuid'])->toBe('123e4567-e89b-12d3-a456-426614174000')
        ->and($data['issueDate'])->toBe('16-02-2025')
        ->and($data['paymentMethod'])->toBe('012')
        ->and($data['note'])->toBe('Test invoice')
        ->and($data['invoiceCounter'])->toBe(1)
        ->and($data['currency'])->toBe('JOD');
});

test('it validates payment method', function () {
    $invoice = new JoFotaraService('test-client-id', 'test-client-secret');

    expect(fn () => $invoice->basicInformation()->setPaymentMethod('invalid'))
        ->toThrow(InvalidArgumentException::class, 'Payment method must be either 012 (cash) or 022 (receivable)');

    expect(fn () => $invoice->basicInformation()->setPaymentMethod('022'))->not->toThrow(InvalidArgumentException::class);
});

test('it validates date format', function () {
    $invoice = new JoFotaraService('test-client-id', 'test-client-secret');

    expect(fn () => $invoice->basicInformation()->setIssueDate('2025-02-16'))
        ->toThrow(InvalidArgumentException::class, 'Date must be in the format dd-mm-yyyy');

    expect(fn () => $invoice->basicInformation()->setIssueDate('16-02-2025'))->not->toThrow(InvalidArgumentException::class);
});

test('it validates invoice counter', function () {
    $invoice = new JoFotaraService('test-client-id', 'test-client-secret');

    expect(fn () => $invoice->basicInformation()->setInvoiceCounter(0))
        ->toThrow(InvalidArgumentException::class, 'Invoice counter must be greater than 0');

    expect(fn () => $invoice->basicInformation()->setInvoiceCounter(1))->not->toThrow(InvalidArgumentException::class);
});

test('it accepts DateTime object for issue date', function () {
    $invoice = new JoFotaraService('test-client-id', 'test-client-secret');
    $date = new DateTime('2025-02-16');

    $invoice->basicInformation()->setIssueDate($date);

    expect($invoice->basicInformation()->toArray()['issueDate'])->toBe('16-02-2025');
});
