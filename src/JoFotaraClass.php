<?php

namespace JBadarneh\JoFotara;

use JBadarneh\JoFotara\Sections\BasicInvoiceInformation;
use JBadarneh\JoFotara\Sections\SellerInformation;
use JBadarneh\JoFotara\Sections\BuyerInformation;
use JBadarneh\JoFotara\Sections\InvoiceItems;
use JBadarneh\JoFotara\Sections\MonetaryTotals;

class JoFotaraClass
{
    private BasicInvoiceInformation $basicInfo;
    private ?SellerInformation $sellerInfo = null;
    private ?BuyerInformation $buyerInfo = null;
    private ?InvoiceItems $items = null;
    private ?MonetaryTotals $monetaryTotals = null;

    public function __construct()
    {
        $this->basicInfo = new BasicInvoiceInformation();
    }

    /**
     * Get the basic invoice information section builder
     *
     * @return BasicInvoiceInformation
     */
    public function basicInformation(): BasicInvoiceInformation
    {
        return $this->basicInfo;
    }

    /**
     * Get the seller information section builder
     *
     * @return SellerInformation
     */
    public function sellerInformation(): SellerInformation
    {
        if (!$this->sellerInfo) {
            $this->sellerInfo = new SellerInformation();
        }
        return $this->sellerInfo;
    }

    /**
     * Get the buyer information section builder
     *
     * @return BuyerInformation
     */
    public function buyerInformation(): BuyerInformation
    {
        if (!$this->buyerInfo) {
            $this->buyerInfo = new BuyerInformation();
        }
        return $this->buyerInfo;
    }

    /**
     * Get the invoice items section builder
     *
     * @return InvoiceItems
     */
    public function items(): InvoiceItems
    {
        if (!$this->items) {
            $this->items = new InvoiceItems();
        }
        return $this->items;
    }

    /**
     * Get the monetary totals section builder
     *
     * @return MonetaryTotals
     */
    public function monetaryTotals(): MonetaryTotals
    {
        if (!$this->monetaryTotals) {
            $this->monetaryTotals = new MonetaryTotals();
        }
        return $this->monetaryTotals;
    }

    /**
     * Generate the complete XML for the invoice
     *
     * @return string The generated XML
     */
    public function generateXml(): string
    {
        $xml = [];
        
        // Add XML declaration
        $xml[] = '<?xml version="1.0" encoding="UTF-8"?>';
        
        // Add root element with namespaces (we'll need to add proper namespaces later)
        $xml[] = '<Invoice>';
        
        // Add basic information
        $xml[] = $this->basicInfo->toXml();
        
        // Add seller information if set
        if ($this->sellerInfo) {
            $xml[] = $this->sellerInfo->toXml();
        }
        
        // Add buyer information if set
        if ($this->buyerInfo) {
            $xml[] = $this->buyerInfo->toXml();
        }
        
        // Add items if set
        if ($this->items) {
            $xml[] = $this->items->toXml();
        }
        
        // Add monetary totals if set
        if ($this->monetaryTotals) {
            $xml[] = $this->monetaryTotals->toXml();
        }
        
        // Close root element
        $xml[] = '</Invoice>';
        
        return implode("\n", $xml);
    }

    /**
     * Send the invoice to the JoFotara API
     *
     * @return array The API response
     */
    public function send(): array
    {
        // This will be implemented to handle API communication
        return [];
    }
}