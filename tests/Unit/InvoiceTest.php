<?php

use JBadarneh\JoFotara\JoFotaraService;

test('it should calculate payable amount correctly when item is discounted with tax', function () {
    // 100 item
    // 20 discount
    // 16 tax
    // Total = 92.8

    $invoice = new JoFotaraService('some-id', 'some-secret');
    $invoice->basicInformation()
        ->setInvoiceId('INV-001')
        ->setUuid('123e4567-e89b-12d3-a456-426614174000')
        ->setIssueDate('16-02-2025')
        ->setInvoiceType('general_sales')
        ->cash();
    $invoice->sellerInformation()
        ->setTin('12345678')
        ->setName('Test Seller');
    $invoice->customerInformation()
        ->setId('123456789', 'TIN')
        ->setTin('123456789')
        ->setName('Test Buyer')
        ->setCityCode('JO-AM')
        ->setPhone('0791234567');

    $invoice->items()
        ->addItem('1')
        ->setQuantity(1)
        ->setUnitPrice(100.0)
        ->setDescription('Test Item')
        ->setDiscount(20)
        ->tax(16);

    $invoice->invoiceTotals();

    /** Verify the totals
     * 'taxExclusiveAmount': refers to the total amount before tax and discounts (100)
     * 'taxInclusiveAmount' : refers to the total amount after tax and discounts (100 - 20) * 1.16 = 92.8
     * 'discountTotalAmount': 20
     * 'taxTotalAmount' : 12.8
     * 'payableAmount' : 92.8
     */
    $totals = $invoice->invoiceTotals()->toArray();
    expect($totals['taxExclusiveAmount'])->toBe(100.0)
        ->and($totals['taxInclusiveAmount'])->toBe((100 - 20) * 1.16)
        ->and($totals['discountTotalAmount'])->toBe(20.0)
        ->and($totals['taxTotalAmount'])->toBe((100 - 20) * 0.16)
        ->and($totals['payableAmount'])->toBe(92.8);
});

test('it should calculate payable amount correctly when item is discounted without tax', function () {
    // 100 item
    // 20 discount
    // 16 tax
    // Total = 92.8

    $invoice = new JoFotaraService('some-id', 'some-secret');
    $invoice->basicInformation()
        ->setInvoiceId('INV-001')
        ->setUuid('123e4567-e89b-12d3-a456-426614174000')
        ->setIssueDate('16-02-2025')
        ->setInvoiceType('income')
        ->cash();
    $invoice->sellerInformation()
        ->setTin('12345678')
        ->setName('Test Seller');
    $invoice->customerInformation()
        ->setId('123456789', 'TIN')
        ->setTin('123456789')
        ->setName('Test Buyer')
        ->setCityCode('JO-AM')
        ->setPhone('0791234567');

    $invoice->items()
        ->addItem('1')
        ->setQuantity(3)
        ->setUnitPrice(100.0)
        ->setDescription('Test Item')
        ->setDiscount(20)
        ->taxExempted();

    $invoice->invoiceTotals();

    /** Verify the totals
     * 'taxExclusiveAmount': refers to the total amount before tax and discounts (100)
     * 'taxInclusiveAmount' : refers to the total amount after tax and discounts (100 - 20) = 80
     * 'discountTotalAmount': 20
     * 'taxTotalAmount' : 0.0
     * 'payableAmount' : 80.0
     */
    $totals = $invoice->invoiceTotals()->toArray();
    expect($totals['taxExclusiveAmount'])->toBe(300.0)
        ->and($totals['taxInclusiveAmount'])->toBe((300.0 - 20.0))
        ->and($totals['discountTotalAmount'])->toBe(20.0)
        ->and($totals['taxTotalAmount'])->toBe(0.0)
        ->and($totals['payableAmount'])->toBe(280.0);
});
