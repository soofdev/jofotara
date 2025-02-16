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
        // This will be implemented to combine XML from all sections
        return '';
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