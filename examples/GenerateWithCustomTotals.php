<?php

require_once __DIR__ . '/../vendor/autoload.php';

use JBadarneh\JoFotara\JoFotaraService;

/**
 * Example: Generate Invoice with Custom Totals
 * 
 * This example demonstrates how to disable totals validation
 * to use custom totals that may differ from calculated values.
 * 
 * Use cases:
 * - External business logic for rounding
 * - Integration with accounting systems with different calculation rules
 * - Special promotional pricing that affects totals
 */

$invoice = new JoFotaraService('your-client-id', 'your-client-secret');

// Configure basic invoice information
$invoice->basicInformation()
    ->setInvoiceId('INV-CUSTOM-001')
    ->setUuid('123e4567-e89b-12d3-a456-426614174000')
    ->setIssueDate('16-02-2025')
    ->setInvoiceType('general_sales')
    ->cash()
    ->setNote('Invoice with custom totals due to promotional pricing');

// Set seller information
$invoice->sellerInformation()
    ->setName('Your Company Name')
    ->setTin('123456789');

// Set customer information
$invoice->customerInformation()
    ->setId('987654321', 'TIN')
    ->setName('Customer Name')
    ->setPostalCode('11937')
    ->setCityCode('JO-AM');

// Set supplier income source
$invoice->supplierIncomeSource('123456789');

// Add invoice items
// Item 1: Standard pricing
$invoice->items()
    ->addItem('1')
    ->setQuantity(2)
    ->setUnitPrice(100.0)
    ->setDescription('Premium Widget')
    ->setDiscount(10.0)  // 10 JOD discount
    ->tax(16);

// Item 2: Another item
$invoice->items()
    ->addItem('2')
    ->setQuantity(1)
    ->setUnitPrice(50.0)
    ->setDescription('Basic Widget')
    ->tax(16);

// Calculated totals would be:
// Item 1: (2 * 100) - 10 = 190 (tax exclusive), tax = 190 * 0.16 = 30.4, total = 220.4
// Item 2: 50 (tax exclusive), tax = 50 * 0.16 = 8, total = 58
// Combined: tax exclusive = 240, tax = 38.4, tax inclusive = 278.4

echo "=== Standard Invoice (with validation) ===\n";
try {
    // This would use calculated totals automatically
    $invoice->invoiceTotals();
    $xml = $invoice->generateXml();
    echo "✓ Generated successfully with calculated totals\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Custom Totals (validation disabled) ===\n";

// Now let's set custom totals that differ from calculated values
// For example, due to a special promotion or external business logic

// DISABLE totals validation to allow custom values
$invoice->disableTotalsValidation();

// Set custom totals (these differ from calculated values)
$invoice->invoiceTotals()
    ->setTaxExclusiveAmount(230.0)    // Custom: 230 instead of calculated 240
    ->setTaxInclusiveAmount(260.0)    // Custom: 260 instead of calculated 278.4
    ->setDiscountTotalAmount(10.0)    // Discount from items
    ->setTaxTotalAmount(30.0)         // Custom: 30 instead of calculated 38.4
    ->setPayableAmount(260.0);        // Custom: 260 instead of calculated 278.4

try {
    $xml = $invoice->generateXml();
    echo "✓ Generated successfully with custom totals\n";
    echo "Custom tax exclusive amount: 230.0 JOD\n";
    echo "Custom tax inclusive amount: 260.0 JOD\n";
    echo "Custom tax amount: 30.0 JOD\n";
    
    // Verify the XML contains our custom values
    if (strpos($xml, '<cbc:TaxExclusiveAmount currencyID="JO">230.000000000</cbc:TaxExclusiveAmount>') !== false) {
        echo "✓ Custom tax exclusive amount found in XML\n";
    }
    
    if (strpos($xml, '<cbc:TaxInclusiveAmount currencyID="JO">260.000000000</cbc:TaxInclusiveAmount>') !== false) {
        echo "✓ Custom tax inclusive amount found in XML\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Re-enabling Validation ===\n";

// Re-enable validation to show it still works
$invoice->enableTotalsValidation();

try {
    $xml = $invoice->generateXml();
    echo "✗ This should have failed!\n";
} catch (Exception $e) {
    echo "✓ Validation correctly prevented mismatched totals: " . $e->getMessage() . "\n";
}

echo "\n=== Alternative: Using setTotalsValidation() ===\n";

// Alternative method to control validation
$invoice->setTotalsValidation(false);

try {
    $xml = $invoice->generateXml();
    echo "✓ Generated successfully using setTotalsValidation(false)\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Usage Summary ===\n";
echo "1. Use ->disableTotalsValidation() to allow custom totals\n";
echo "2. Use ->enableTotalsValidation() to restore normal validation\n";
echo "3. Use ->setTotalsValidation(bool) for programmatic control\n";
echo "4. All methods support method chaining\n";
echo "5. Individual field validation (negative amounts, etc.) still applies\n";

// Optional: Output the final XML
if (isset($xml)) {
    echo "\n=== Generated XML (Base64 Encoded) ===\n";
    echo base64_encode($xml) . "\n";
} 