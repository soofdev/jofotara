<?php

require_once __DIR__ . '/../vendor/autoload.php';

use JBadarneh\JoFotara\JoFotaraService;

$invoice = new JoFotaraService('test-client-id', 'test-client-secret');

// Set up a basic invoice
$invoice->basicInformation()
    ->setInvoiceId('001')
    ->setUuid('123e4567-e89b-12d3-a456-111111111111')
    ->setIssueDate('17-02-2020')
    ->cash();

$invoice->sellerInformation()
    ->setTin('12345678')
    ->setName('شركة تجريبية');

// Customer information
$invoice->buyerInformation()
    ->setTin('32165498')
    ->setName('الزبون التجريبي')
    ->setCityCode('JO-IR')
    ->setPhone('0791234567');

$invoice->supplierIncomeSource('12345678');

$invoice->items()
    ->addItem('1')
    ->setQuantity(1)
    ->setUnitPrice(1.0)
    ->setDescription('منتج تجريبي')
    ->taxExempted();

$invoice->invoiceTotals();

$encodedInvoice = $invoice->encodeInvoice();

echo '"'.$encodedInvoice.'"'."\n";
