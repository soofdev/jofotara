# PHP SDK for Jordan E-Invoice Portal (JoFotara)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jafar-albadarneh/jofotara.svg?style=flat-square)](https://packagist.org/packages/jafar-albadarneh/jofotara)
[![Tests](https://img.shields.io/github/actions/workflow/status/jafar-albadarneh/jofotara/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/jafar-albadarneh/jofotara/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/jafar-albadarneh/jofotara.svg?style=flat-square)](https://packagist.org/packages/jafar-albadarneh/jofotara)

A developer-friendly PHP SDK for seamless integration with Jordan's e-invoicing system (JoFotara). This package handles XML generation, validation, and API communication with a fluent, intuitive interface.

## Quick Start

```php
use JBadarneh\JoFotara\JoFotaraService;

$invoice = new JoFotaraService('your-client-id', 'your-client-secret');

// Set basic invoice information
$invoice->basicInformation()
    ->setInvoiceId('INV-001')
    ->setUuid('123e4567-e89b-12d3-a456-426614174000')
    ->setIssueDate('16-02-2025')
    ->cash();  // or ->receivable()
 
// Set seller information
$invoice->sellerInformation()
    ->setName('Your Company')
    ->setTin('123456789');
    
// Set buyer information
$invoice->buyerInformation()
    ->setId('987654321', 'TIN')
    ->setName('Customer Name')
    ->setPostalCode('11937')
    ->setCityCode('JO-IR')
    ->setPhone('0791234567')
    ->setTin('987654321');
    
// Set Supplier Income Source
$invoice->supplierIncomeSource('123456789');

// Add items with automatic tax calculation
$invoice->items()
    ->addItem('1')
    ->setQuantity(2)
    ->setUnitPrice(100.0)
    ->setDescription('Premium Widget')
    ->tax(16);  // 16% VAT

// Send to JoFotara
$response = $invoice->send();
```

## Installation

Install the package via composer:

```bash
composer require jafar-albadarneh/jofotara
```

## Detailed Configuration

### Basic Invoice Information

```php
$invoice->basicInformation()
    ->setInvoiceId('INV-001')           // Required: Your unique invoice ID
    ->setUuid('123e4567-...')           // Required: UUID v4 format
    ->setIssueDate('16-02-2025')        // Required: Format dd-mm-yyyy
    ->cash()                            // Payment method: cash (012)
    // or
    ->receivable()                      // Payment method: receivable (022)
    ->setNote('Optional note')          // Optional: Invoice note
    ->setInvoiceCounter(1);             // Optional: Sequential counter (ICV)
```

### Seller Information

```php
// Configure default seller info (recommended)
SellerInformation::configureDefaults(
    tin: '123456789',
    name: 'Your Company'
);

// Or set per invoice
$invoice->sellerInformation()
    ->setName('Your Company')           // Required: Company name
    ->setTin('123456789');             // Required: Tax ID Number
```

### Buyer Information

```php
$invoice->buyerInformation()
    ->setId('987654321', 'TIN')        // Required: ID and type (TIN, NIN, or PN)
    ->setName('Customer Name')          // Required for receivables
    ->setPostalCode('11937')           // Optional
    ->setCityCode('JO-IR')             // Optional: Jordan city code
    ->setPhone('0791234567')           // Optional
    ->setTin('987654321');             // Optional
```

### Supplier Income Source

The supplier income source sequence (تسلسل مصدر الدخل) is a required value that must be set for each invoice. This value is obtained from your JoFotara portal and represents your business's income source sequence number.

```php
// Set the supplier income source sequence
$invoice->supplierIncomeSource('123456789');
```

> **Important**: The supplier income source sequence is mandatory and must be set before generating the invoice XML. You can find this value in the table where it shows your client ID and secret, under column "تسلسل مصدر الدخل".

Example usage in a complete invoice:

```php
$invoice = new JoFotaraService('your-client-id', 'your-client-secret');

// Set basic information
$invoice->basicInformation()
    ->setInvoiceId('INV-001')
    ->setIssueDate('2024-03-20')
    ->cash();

// Set seller information
$invoice->sellerInformation()
    ->setName('Your Company')
    ->setTin('123456789');

// Set buyer information
$invoice->buyerInformation()
    ->setId('987654321', 'TIN')
    ->setName('Customer Name');

// Set supplier income source (required)
$invoice->supplierIncomeSource('123456789');

// Add items...
$invoice->items()
    ->addItem('1')
    ->setQuantity(1)
    ->setUnitPrice(100.0)
    ->setDescription('Product')
    ->tax(16);
```

The supplier income source sequence is used by the JoFotara system to:
- Track your business's income sources
- Validate invoice submissions
- Ensure proper tax reporting

### Invoice Items and Tax Handling

```php
// Standard VAT item (16%)
$invoice->items()
    ->addItem('1')
    ->setQuantity(2)
    ->setUnitPrice(100.0)
    ->setDescription('Premium Widget')
    ->tax(16);

// Tax exempt item
$invoice->items()
    ->addItem('2')
    ->setQuantity(1)
    ->setUnitPrice(50.0)
    ->setDescription('Basic Widget')
    ->taxExempted();

// Zero-rated item (e.g., exports)
$invoice->items()
    ->addItem('3')
    ->setQuantity(1)
    ->setUnitPrice(75.0)
    ->setDescription('Export Widget')
    ->zeroTax();

// Item with discount
$invoice->items()
    ->addItem('4')
    ->setQuantity(1)
    ->setUnitPrice(200.0)
    ->setDescription('Discounted Widget')
    ->setDiscount(20.0)  // 20 JOD discount
    ->tax(16);
```

### Automatic Total Calculations

The SDK automatically calculates all invoice totals based on the items you add:

- Tax exclusive amount (before tax)
- Tax inclusive amount (after tax)
- Total discounts
- Total tax amount
- Final payable amount

For special cases, you can manually set totals:

```php
$invoice->invoiceTotals()
    ->setTaxExclusiveAmount(100.0)
    ->setTaxInclusiveAmount(116.0)
    ->setDiscountTotalAmount(20.0)
    ->setTaxTotalAmount(16.0)
    ->setPayableAmount(96.0);
```

> **Note**: When manually setting totals, they must match the calculated values from the items, or an exception will be thrown to ensure data integrity.

## Validation

The SDK includes comprehensive validation to ensure your invoice meets JoFotara requirements:

- All required fields are present and properly formatted
- Date formats follow dd-mm-yyyy pattern
- Tax calculations are accurate and consistent
- Totals match line items
- Valid city codes and tax categories

Validation errors throw `InvalidArgumentException` with descriptive messages to help you quickly identify and fix issues.

## API Communication

The `send()` method handles the complete flow:

1. XML generation and validation
2. Base64 encoding
3. API authentication
4. Error handling

```php
try {
    $response = $invoice->send();
    $qrCode = $response['qrCode'];
} catch (InvalidArgumentException $e) {
    // Handle validation errors
    echo $e->getMessage();
} catch (RuntimeException $e) {
    // Handle API communication errors
    echo $e->getMessage();
}
```

## Testing

```bash
vendor/bin/pest
```

## Contributing

Contributions are welcome! Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security-related issues, please email security@jbadarneh.com instead of using the issue tracker.

## Credits

- [Jafar Albadarneh](https://github.com/jafar-albadarneh)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
