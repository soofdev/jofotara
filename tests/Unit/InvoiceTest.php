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

test('it can disable totals validation to allow manual totals', function () {
    $invoice = new JoFotaraService('test-client-id', 'test-client-secret');

    // Set up basic invoice info
    $invoice->basicInformation()
        ->setInvoiceId('INV-001')
        ->setUuid('123e4567-e89b-12d3-a456-426614174000')
        ->setIssueDate('16-02-2025')
        ->setInvoiceType('income')
        ->cash();

    // Add required seller info
    $invoice->sellerInformation()
        ->setName('Seller Company')
        ->setTin('12345678');

    // Add required buyer info
    $invoice->customerInformation()
        ->setId('987654321', 'TIN')
        ->setName('Customer 123');

    $invoice->supplierIncomeSource('12345678');

    // Add item with calculated totals: tax-exclusive=100, tax=16, tax-inclusive=116
    $invoice->items()
        ->addItem('1')
        ->setQuantity(1)
        ->setUnitPrice(100.0)
        ->setDescription('Test Item')
        ->tax(16);

    // Disable totals validation
    $invoice->disableTotalsValidation();

    // Set different totals manually (these would normally fail validation)
    $invoice->invoiceTotals()
        ->setTaxExclusiveAmount(90.0)  // Different from calculated 100
        ->setTaxInclusiveAmount(100.0) // Different from calculated 116
        ->setTaxTotalAmount(10.0)      // Different from calculated 16
        ->setPayableAmount(100.0);     // Different from calculated 116

    // This should not throw an exception
    expect(fn () => $invoice->generateXml())->not->toThrow(InvalidArgumentException::class);

    // XML should be generated successfully
    $xml = $invoice->generateXml();
    expect($xml)->toContain('<cbc:TaxExclusiveAmount currencyID="JO">90.000000000</cbc:TaxExclusiveAmount>');
    expect($xml)->toContain('<cbc:TaxInclusiveAmount currencyID="JO">100.000000000</cbc:TaxInclusiveAmount>');
});

test('it can re-enable totals validation after disabling', function () {
    $invoice = new JoFotaraService('test-client-id', 'test-client-secret');

    // Set up basic invoice info
    $invoice->basicInformation()
        ->setInvoiceId('INV-001')
        ->setUuid('123e4567-e89b-12d3-a456-426614174000')
        ->setIssueDate('16-02-2025')
        ->setInvoiceType('income')
        ->cash();

    // Add required seller info
    $invoice->sellerInformation()
        ->setName('Seller Company')
        ->setTin('12345678');

    // Add required buyer info
    $invoice->customerInformation()
        ->setId('987654321', 'TIN')
        ->setName('Customer 123');

    $invoice->supplierIncomeSource('12345678');

    // Add item
    $invoice->items()
        ->addItem('1')
        ->setQuantity(1)
        ->setUnitPrice(100.0)
        ->setDescription('Test Item')
        ->tax(16);

    // Disable then re-enable validation
    $invoice->disableTotalsValidation()
             ->enableTotalsValidation();

    // Set invalid totals manually
    $invoice->invoiceTotals()
        ->setTaxExclusiveAmount(90.0)  // Should be 100
        ->setTaxInclusiveAmount(100.0) // Should be 116
        ->setTaxTotalAmount(10.0)      // Should be 16
        ->setPayableAmount(100.0);     // Should be 116

    // This should throw an exception since validation is re-enabled
    expect(fn () => $invoice->generateXml())
        ->toThrow(InvalidArgumentException::class, 'Invoice totals do not match calculated values from line items');
});

test('it supports setTotalsValidation method', function () {
    $invoice = new JoFotaraService('test-client-id', 'test-client-secret');

    // Set up basic invoice info
    $invoice->basicInformation()
        ->setInvoiceId('INV-001')
        ->setUuid('123e4567-e89b-12d3-a456-426614174000')
        ->setIssueDate('16-02-2025')
        ->setInvoiceType('income')
        ->cash();

    // Add required seller info
    $invoice->sellerInformation()
        ->setName('Seller Company')
        ->setTin('12345678');

    // Add required buyer info
    $invoice->customerInformation()
        ->setId('987654321', 'TIN')
        ->setName('Customer 123');

    $invoice->supplierIncomeSource('12345678');

    // Add item
    $invoice->items()
        ->addItem('1')
        ->setQuantity(1)
        ->setUnitPrice(100.0)
        ->setDescription('Test Item')
        ->tax(16);

    // Use setTotalsValidation to disable
    $invoice->setTotalsValidation(false);

    // Set different totals manually
    $invoice->invoiceTotals()
        ->setTaxExclusiveAmount(90.0)
        ->setTaxInclusiveAmount(100.0)
        ->setTaxTotalAmount(10.0)
        ->setPayableAmount(100.0);

    // Should not throw
    expect(fn () => $invoice->generateXml())->not->toThrow(InvalidArgumentException::class);

    // Re-enable with setTotalsValidation
    $invoice->setTotalsValidation(true);

    // Should now throw
    expect(fn () => $invoice->generateXml())
        ->toThrow(InvalidArgumentException::class, 'Invoice totals do not match calculated values from line items');
});

test('validation methods return self for method chaining', function () {
    $invoice = new JoFotaraService('test-client-id', 'test-client-secret');

    // Test method chaining
    $result = $invoice->disableTotalsValidation()
                     ->enableTotalsValidation()
                     ->setTotalsValidation(false);

    expect($result)->toBe($invoice);
});
