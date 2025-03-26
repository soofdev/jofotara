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
        ->toThrow(InvalidArgumentException::class, 'Payment method must be one of: 011, 021 (Income), 012, 022 (General Sales), 013, 023 (Special Sales)')
        ->and(fn () => $invoice->basicInformation()->setPaymentMethod('022'))->not->toThrow(InvalidArgumentException::class);
});

test('it validates date format', function () {
    $invoice = new JoFotaraService('test-client-id', 'test-client-secret');

    expect(fn () => $invoice->basicInformation()->setIssueDate('2025-02-16'))
        ->toThrow(InvalidArgumentException::class, 'Date must be in the format dd-mm-yyyy')
        ->and(fn () => $invoice->basicInformation()->setIssueDate('16-02-2025'))->not->toThrow(InvalidArgumentException::class);

});

test('it validates invoice counter', function () {
    $invoice = new JoFotaraService('test-client-id', 'test-client-secret');

    expect(fn () => $invoice->basicInformation()->setInvoiceCounter(0))
        ->toThrow(InvalidArgumentException::class, 'Invoice counter must be greater than 0')
        ->and(fn () => $invoice->basicInformation()->setInvoiceCounter(1))->not->toThrow(InvalidArgumentException::class);

});

test('it accepts DateTime object for issue date', function () {
    $invoice = new JoFotaraService('test-client-id', 'test-client-secret');
    $date = new DateTime('2025-02-16');

    $invoice->basicInformation()->setIssueDate($date);

    expect($invoice->basicInformation()->toArray()['issueDate'])->toBe('16-02-2025');
});

test('it validates invoice type', function () {
    $invoice = new JoFotaraService('test-client-id', 'test-client-secret');

    expect(fn () => $invoice->basicInformation()->setInvoiceType('invalid'))
        ->toThrow(InvalidArgumentException::class, "Invoice type must be one of: 'income', 'general_sales', 'special_sales'")
        ->and(fn () => $invoice->basicInformation()->setInvoiceType('income'))->not->toThrow(InvalidArgumentException::class)
        ->and(fn () => $invoice->basicInformation()->setInvoiceType('general_sales'))->not->toThrow(InvalidArgumentException::class)
        ->and(fn () => $invoice->basicInformation()->setInvoiceType('special_sales'))->not->toThrow(InvalidArgumentException::class);
});

test('it sets correct payment method code based on invoice type for cash', function () {
    $invoice = new JoFotaraService('test-client-id', 'test-client-secret');
    
    // Income invoice with cash payment
    $invoice->basicInformation()->setInvoiceType('income')->cash();
    expect($invoice->basicInformation()->toArray()['paymentMethod'])->toBe('011');
    
    // General sales invoice with cash payment
    $invoice->basicInformation()->setInvoiceType('general_sales')->cash();
    expect($invoice->basicInformation()->toArray()['paymentMethod'])->toBe('012');
    
    // Special sales invoice with cash payment
    $invoice->basicInformation()->setInvoiceType('special_sales')->cash();
    expect($invoice->basicInformation()->toArray()['paymentMethod'])->toBe('013');
});

test('it sets correct payment method code based on invoice type for receivable', function () {
    $invoice = new JoFotaraService('test-client-id', 'test-client-secret');
    
    // Income invoice with receivable payment
    $invoice->basicInformation()->setInvoiceType('income')->receivable();
    expect($invoice->basicInformation()->toArray()['paymentMethod'])->toBe('021');
    
    // General sales invoice with receivable payment
    $invoice->basicInformation()->setInvoiceType('general_sales')->receivable();
    expect($invoice->basicInformation()->toArray()['paymentMethod'])->toBe('022');
    
    // Special sales invoice with receivable payment
    $invoice->basicInformation()->setInvoiceType('special_sales')->receivable();
    expect($invoice->basicInformation()->toArray()['paymentMethod'])->toBe('023');
});

test('it defaults to income invoice type when not explicitly set', function () {
    $invoice = new JoFotaraService('test-client-id', 'test-client-secret');
    
    // Cash payment without setting invoice type
    $invoice->basicInformation()->cash();
    $data = $invoice->basicInformation()->toArray();
    expect($data['invoiceType'])->toBe('income')
        ->and($data['paymentMethod'])->toBe('011');
    
    // New instance for receivable test
    $invoice2 = new JoFotaraService('test-client-id', 'test-client-secret');
    $invoice2->basicInformation()->receivable();
    $data2 = $invoice2->basicInformation()->toArray();
    expect($data2['invoiceType'])->toBe('income')
        ->and($data2['paymentMethod'])->toBe('021');
});
