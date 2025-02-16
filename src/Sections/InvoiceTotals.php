<?php

namespace JBadarneh\JoFotara\Sections;

use InvalidArgumentException;
use JBadarneh\JoFotara\Traits\XmlHelperTrait;

class InvoiceTotals
{
    use XmlHelperTrait;

    private float $taxExclusiveAmount = 0.0;
    private float $taxInclusiveAmount = 0.0;
    private float $discountTotalAmount = 0.0;
    private float $taxTotalAmount = 0.0;
    private float $payableAmount = 0.0;

    /**
     * Set the total amount before tax and discounts
     * 
     * @param float $amount The tax exclusive amount
     * @return self
     * @throws InvalidArgumentException If amount is negative
     */
    public function setTaxExclusiveAmount(float $amount): self
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Tax exclusive amount cannot be negative');
        }

        $this->taxExclusiveAmount = $amount;
        return $this;
    }

    /**
     * Set the total amount including tax
     * 
     * @param float $amount The tax inclusive amount
     * @return self
     * @throws InvalidArgumentException If amount is less than tax exclusive amount
     */
    public function setTaxInclusiveAmount(float $amount): self
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Tax inclusive amount cannot be negative');
        }

        if ($amount < $this->taxExclusiveAmount) {
            throw new InvalidArgumentException('Tax inclusive amount cannot be less than tax exclusive amount');
        }

        $this->taxInclusiveAmount = $amount;
        return $this;
    }

    /**
     * Set the total discount amount
     * Note: Discounts must be distributed to goods/services, not applied to the total invoice
     * If not set, defaults to 0
     * 
     * @param float|null $amount The total discount amount
     * @return self
     * @throws InvalidArgumentException If amount is negative or greater than tax exclusive amount
     */
    public function setDiscountTotalAmount(?float $amount = null): self
    {
        $amount = $amount ?? 0.0;

        if ($amount < 0) {
            throw new InvalidArgumentException('Discount total amount cannot be negative');
        }

        if ($amount > $this->taxExclusiveAmount) {
            throw new InvalidArgumentException('Discount total amount cannot be greater than tax exclusive amount');
        }

        $this->discountTotalAmount = $amount;
        return $this;
    }

    /**
     * Set the total tax amount
     * 
     * @param float $amount The total tax amount
     * @return self
     * @throws InvalidArgumentException If amount is negative or if it makes tax inclusive amount invalid
     */
    public function setTaxTotalAmount(float $amount): self
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Tax total amount cannot be negative');
        }

        if ($this->taxInclusiveAmount > 0 && ($this->taxExclusiveAmount + $amount) > $this->taxInclusiveAmount) {
            throw new InvalidArgumentException('Tax total amount would make tax inclusive amount invalid');
        }

        $this->taxTotalAmount = $amount;
        return $this;
    }

    /**
     * Set the final payable amount
     * 
     * @param float $amount The payable amount
     * @return self
     * @throws InvalidArgumentException If amount is negative or less than tax inclusive amount minus allowances
     */
    public function setPayableAmount(float $amount): self
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Payable amount cannot be negative');
        }

        if ($this->taxInclusiveAmount > 0 && $amount < ($this->taxInclusiveAmount - $this->discountTotalAmount)) {
            throw new InvalidArgumentException('Payable amount cannot be less than tax inclusive amount minus allowances');
        }

        $this->payableAmount = $amount;
        return $this;
    }

    /**
     * Convert the invoice totals to XML
     * 
     * @return string The XML representation
     */
    public function toXml(): string
    {
        if ($this->taxInclusiveAmount === 0.0) {
            throw new InvalidArgumentException('Tax inclusive amount is required');
        }
        if ($this->taxExclusiveAmount === 0.0) {
            throw new InvalidArgumentException('Tax exclusive amount is required');
        }
        if ($this->payableAmount === 0.0) {
            throw new InvalidArgumentException('Payable amount is required');
        }

        $xml = [];

        // Discount section
        if ($this->discountTotalAmount > 0) {
            $xml[] = '<cac:AllowanceCharge>';
            $xml[] = '    <cbc:ChargeIndicator>false</cbc:ChargeIndicator>';
            $xml[] = '    <cbc:AllowanceChargeReason>discount</cbc:AllowanceChargeReason>';
            $xml[] = sprintf('    <cbc:Amount currencyID="JOD">%.2f</cbc:Amount>', $this->discountTotalAmount);
            $xml[] = '</cac:AllowanceCharge>';
        }

        // Tax total
        $xml[] = '<cac:TaxTotal>';
        $xml[] = sprintf('    <cbc:TaxAmount currencyID="JOD">%.2f</cbc:TaxAmount>', $this->taxTotalAmount);
        $xml[] = '</cac:TaxTotal>';

        // Monetary totals
        $xml[] = '<cac:LegalMonetaryTotal>';
        $xml[] = sprintf('    <cbc:TaxExclusiveAmount currencyID="JOD">%.2f</cbc:TaxExclusiveAmount>', 
            $this->taxExclusiveAmount
        );
        $xml[] = sprintf('    <cbc:TaxInclusiveAmount currencyID="JOD">%.2f</cbc:TaxInclusiveAmount>', 
            $this->taxInclusiveAmount
        );
        if ($this->discountTotalAmount > 0) {
            $xml[] = sprintf('    <cbc:AllowanceTotalAmount currencyID="JOD">%.2f</cbc:AllowanceTotalAmount>', 
                $this->discountTotalAmount
            );
        }
        $xml[] = sprintf('    <cbc:PayableAmount currencyID="JOD">%.2f</cbc:PayableAmount>', 
            $this->payableAmount
        );
        $xml[] = '</cac:LegalMonetaryTotal>';

        return implode("\n", $xml);
    }

    /**
     * Get the current state as an array
     * This is mainly used for testing purposes
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'taxExclusiveAmount' => $this->taxExclusiveAmount,
            'taxInclusiveAmount' => $this->taxInclusiveAmount,
            'discountTotalAmount' => $this->discountTotalAmount,
            'taxTotalAmount' => $this->taxTotalAmount,
            'payableAmount' => $this->payableAmount,
        ];
    }
}
