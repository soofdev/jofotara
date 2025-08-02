<?php

namespace JBadarneh\JoFotara;

use InvalidArgumentException;
use JBadarneh\JoFotara\Response\JoFotaraResponse;
use JBadarneh\JoFotara\Sections\BasicInvoiceInformation;
use JBadarneh\JoFotara\Sections\CustomerInformation;
use JBadarneh\JoFotara\Sections\InvoiceItems;
use JBadarneh\JoFotara\Sections\InvoiceTotals;
use JBadarneh\JoFotara\Sections\ReasonForReturn;
use JBadarneh\JoFotara\Sections\SellerInformation;
use JBadarneh\JoFotara\Sections\SupplierIncomeSource;
use RuntimeException;

class JoFotaraService
{
    private const API_URL = 'https://backend.jofotara.gov.jo/core/invoices/';

    private BasicInvoiceInformation $basicInfo;

    private ?SellerInformation $sellerInfo = null;

    private ?CustomerInformation $customerInfo = null;

    private ?SupplierIncomeSource $supplierIncomeSource = null;

    private ?InvoiceItems $items = null;

    private ?InvoiceTotals $invoiceTotals = null;

    private ?ReasonForReturn $reasonForReturn = null;

    private string $clientId;

    private string $clientSecret;

    /**
     * Whether to validate that invoice totals match calculated values from line items
     */
    private bool $validateTotals = true;

    public function __construct(string $clientId, string $clientSecret)
    {
        if (empty($clientId) || empty($clientSecret)) {
            throw new InvalidArgumentException('JoFotara client ID and secret are required');
        }

        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->basicInfo = new BasicInvoiceInformation;
    }

    /**
     * Get the basic invoice information section builder
     */
    public function basicInformation(): BasicInvoiceInformation
    {
        return $this->basicInfo;
    }

    /**
     * Get the seller information section builder
     */
    public function sellerInformation(): SellerInformation
    {
        if (! $this->sellerInfo) {
            $this->sellerInfo = new SellerInformation;
        }

        return $this->sellerInfo;
    }

    /**
     * Get the customer information section builder
     */
    public function customerInformation(): CustomerInformation
    {
        if (! $this->customerInfo) {
            $this->customerInfo = new CustomerInformation;
            $this->customerInfo->setId('', 'NIN');
        }

        return $this->customerInfo;
    }

    /**
     * Get the supplier information section builder
     */
    public function supplierIncomeSource(string $sequence): SupplierIncomeSource
    {
        if (! $this->supplierIncomeSource) {
            $this->supplierIncomeSource = new SupplierIncomeSource($sequence);
        }

        return $this->supplierIncomeSource;
    }

    /**
     * Get the invoice items section builder
     */
    public function items(): InvoiceItems
    {
        if (! $this->items) {
            $this->items = new InvoiceItems;
        }

        return $this->items;
    }

    /**
     * Set the reason for return
     *
     * @param  string  $reason  The reason for returning the invoice
     */
    public function setReasonForReturn(string $reason): self
    {
        if (! $this->reasonForReturn) {
            $this->reasonForReturn = new ReasonForReturn;
        }

        $this->reasonForReturn->setReason($reason);

        return $this;
    }

    /**
     * Get the monetary totals section builder
     */
    public function invoiceTotals(): InvoiceTotals
    {
        if (! $this->invoiceTotals) {
            $this->invoiceTotals = new InvoiceTotals;

            // If we have items, calculate totals from them
            if ($this->items && count($this->items->getItems()) > 0) {
                $amountBeforeDiscount = 0.0;
                $totalTaxAmount = 0.0;
                $discountTotalAmount = 0.0;

                foreach ($this->items->getItems() as $item) {
                    $amountBeforeDiscount += $item->getAmountBeforeDiscount();
                    $totalTaxAmount += $item->getTaxAmount();
                    $discountTotalAmount += $item->getDiscount();
                }

                $taxInclusiveAmount = $amountBeforeDiscount + $totalTaxAmount - $discountTotalAmount;
                $payableAmount = $taxInclusiveAmount;

                $this->invoiceTotals
                    ->setTaxExclusiveAmount($amountBeforeDiscount)
                    ->setDiscountTotalAmount($discountTotalAmount)
                    ->setTaxInclusiveAmount($taxInclusiveAmount)
                    ->setTaxTotalAmount($totalTaxAmount)
                    ->setPayableAmount($payableAmount);
            }
        }

        return $this->invoiceTotals;
    }

    /**
     * Validate that all sections are consistent and complete
     *
     * @throws InvalidArgumentException If there are inconsistencies or missing sections
     */
    private function validateSections(): void
    {
        // Validate basic information first
        $this->basicInfo->validateSection();

        // Validate credit invoice requirements before other validations
        if ($this->basicInfo->isCreditInvoice()) {
            if (empty($this->reasonForReturn)) {
                throw new InvalidArgumentException('Credit invoices require a reason for return');
            }
            $this->reasonForReturn->validateSection();
        }

        // Validate all required sections are initialized
        if (! $this->sellerInfo) {
            throw new InvalidArgumentException('Seller information is required');
        }

        // Validate customer information is initialized
        if (! $this->customerInfo) {
            $this->customerInfo = new CustomerInformation;
            $this->customerInfo->setupAnonymousCustomer();
        }

        if (! $this->supplierIncomeSource) {
            throw new InvalidArgumentException('Supplier income source is required');
        }
        if (! $this->items) {
            throw new InvalidArgumentException('At least one invoice item is required');
        }
        if (! $this->invoiceTotals) {
            throw new InvalidArgumentException('Invoice totals are required');
        }

        // Validate each section individually
        $this->sellerInfo->validateSection();
        // Validate customer information if set
        if ($this->customerInfo) {
            $this->customerInfo->validateSection();
        }
        $this->supplierIncomeSource->validateSection();
        $this->items->validateSection();
        $this->invoiceTotals->validateSection();

        // Cross-section validation (totals matching items)
        if ($this->items && $this->invoiceTotals && $this->validateTotals) {
            $items = $this->items->getItems();
            if (count($items) > 0) {
                $calculatedTotals = new InvoiceTotals;

                $amountBeforeDiscount = 0.0;
                $taxTotalAmount = 0.0;
                $discountTotalAmount = 0.0;

                foreach ($items as $item) {
                    $amountBeforeDiscount += $item->getAmountBeforeDiscount();
                    $taxTotalAmount += $item->getTaxAmount();
                    $discountTotalAmount += $item->getDiscount();
                }

                $taxInclusiveAmount = $amountBeforeDiscount - $discountTotalAmount + $taxTotalAmount;
                $payableAmount = $taxInclusiveAmount;

                $calculatedTotals
                    // Set the base amount
                    ->setTaxExclusiveAmount($amountBeforeDiscount)
                    // Set the discount amount
                    ->setDiscountTotalAmount($discountTotalAmount)
                    // Set the tax amount
                    ->setTaxTotalAmount($taxTotalAmount)
                    // Set the final payable amount
                    ->setTaxInclusiveAmount($taxInclusiveAmount)
                    ->setPayableAmount($payableAmount);

                $providedTotals = $this->invoiceTotals->toArray();
                $expectedTotals = $calculatedTotals->toArray();

                if ($providedTotals !== $expectedTotals) {
                    throw new InvalidArgumentException('Invoice totals do not match calculated values from line items');
                }
            }
        }
    }

    public function generateXml(): string
    {
        // Validate sections before generating XML
        $this->validateSections();

        $xml = [];

        // Add XML declaration
        $xml[] = '<?xml version="1.0" encoding="UTF-8"?>';

        // Add root element with namespaces UBL2.1 standard
        $xml[] = '<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2">';

        // Add UBLVersionID
        $xml[] = '<cbc:UBLVersionID>2.1</cbc:UBLVersionID>';

        // Add basic information
        $xml[] = $this->basicInfo->toXml();

        // Add seller information if set
        if ($this->sellerInfo) {
            $xml[] = $this->sellerInfo->toXml();
        }

        // Add customer information if set
        if ($this->customerInfo) {
            $xml[] = $this->customerInfo->toXml();
        }

        // Add Supplier information if set
        if ($this->supplierIncomeSource) {
            $xml[] = $this->supplierIncomeSource->toXml();
        }

        // Add reason for return for credit invoices
        if ($this->basicInfo->isCreditInvoice() && $this->reasonForReturn) {
            $xml[] = $this->reasonForReturn->toXml();
        }

        // Add invoice totals
        $xml[] = $this->invoiceTotals->toXml();

        // Add items if set
        if ($this->items) {
            $xml[] = $this->items->toXml();
        }

        // Close root element
        $xml[] = '</Invoice>';

        return implode("\n", $xml);
    }

    /**
     * Encode the XML invoice to base64
     *
     * @return string Base64 encoded XML
     *
     * @throws InvalidArgumentException If XML generation fails
     */
    public function encodeInvoice(): string
    {
        $xml = $this->generateXml();

        return base64_encode($xml);
    }

    /**
     * Send the invoice to the JoFotara API
     *
     * @return JoFotaraResponse A response object containing the API response data
     *
     * @throws InvalidArgumentException If configuration is missing
     * @throws RuntimeException If the API request fails
     */
    public function send(): JoFotaraResponse
    {
        $encodedInvoice = $this->encodeInvoice();

        $curlHandle = curl_init(self::API_URL);
        curl_setopt_array($curlHandle, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Client-Id: '.$this->clientId,
                'Secret-Key: '.$this->clientSecret,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'invoice' => $encodedInvoice,
            ]),
        ]);

        $response = curl_exec($curlHandle);
        $statusCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
        $error = curl_error($curlHandle);
        curl_close($curlHandle);

        if ($error) {
            throw new RuntimeException('Failed to send invoice: '.$error);
        }

        // For 403 responses, create a response object with authentication error
        if ($statusCode === 403) {
            return new JoFotaraResponse([
                'error' => 'Authentication failed. Please check your client ID and secret.',
                'code' => 'AUTH_ERROR',
            ], $statusCode);
        }

        // Parse the response for other status codes
        $result = json_decode($response, true);

        // For empty responses or parsing errors, provide appropriate error message
        if (empty($response) || json_last_error() !== JSON_ERROR_NONE) {
            $result = [
                'error' => empty($response) ?
                    'Empty response from API' :
                    'Invalid response format from API',
                'code' => 'RESPONSE_ERROR',
            ];
        }

        // Handle 200 and 400 responses with the JoFotaraResponse object
        if ($statusCode !== 200 && $statusCode !== 400 && $statusCode !== 403) {
            throw new RuntimeException('API request failed with status code '.$statusCode);
        }

        // Create a response object that can handle success, error, and auth failure responses
        return new JoFotaraResponse($result, $statusCode);
    }

    /**
     * Disable totals validation to allow using manually set totals without
     * cross-validation against calculated values from line items.
     * 
     * This is useful when you need to set exact totals that may differ
     * from calculated values due to external business logic or rounding.
     */
    public function disableTotalsValidation(): self
    {
        $this->validateTotals = false;
        return $this;
    }

    /**
     * Enable totals validation (default behavior).
     * 
     * When enabled, manually set totals must match calculated values
     * from line items or an exception will be thrown.
     */
    public function enableTotalsValidation(): self
    {
        $this->validateTotals = true;
        return $this;
    }

    /**
     * Set whether to validate totals against calculated values.
     */
    public function setTotalsValidation(bool $validate): self
    {
        $this->validateTotals = $validate;
        return $this;
    }
}
