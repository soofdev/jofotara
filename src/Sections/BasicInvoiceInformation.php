<?php

namespace JBadarneh\JoFotara\Sections;

use DateTime;
use InvalidArgumentException;

class BasicInvoiceInformation
{
    private string $invoiceId;
    private string $uuid;
    private DateTime $issueDate;
    private string $paymentMethod = '012'; // Default to cash payment
    private ?string $note = null;
    private string $currency = 'JOD';
    private int $invoiceCounter = 1;

    /**
     * Set the invoice ID (required)
     *
     * @param string $invoiceId The unique identifier for this invoice
     * @return self
     */
    public function setInvoiceId(string $invoiceId): self
    {
        $this->invoiceId = $invoiceId;
        return $this;
    }

    /**
     * Set the invoice UUID (required)
     * This should be a unique identifier that, combined with the invoice ID,
     * forms a primary key to prevent invoice duplication
     *
     * @param string $uuid The UUID for this invoice
     * @return self
     */
    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;
        return $this;
    }

    /**
     * Set the invoice issue date (required)
     * The date must be in the format dd-mm-yyyy
     *
     * @param string|DateTime $date The invoice issue date
     * @return self
     * @throws InvalidArgumentException If the date format is invalid
     */
    public function setIssueDate(string|DateTime $date): self
    {
        if (is_string($date)) {
            $dateTime = DateTime::createFromFormat('d-m-Y', $date);
            if (!$dateTime) {
                throw new InvalidArgumentException('Date must be in the format dd-mm-yyyy');
            }
            $this->issueDate = $dateTime;
        } else {
            $this->issueDate = $date;
        }
        return $this;
    }

    /**
     * Set the payment method (optional)
     * 012 for cash payment
     * 022 for receivable payment
     *
     * @param string $method The payment method code
     * @return self
     * @throws InvalidArgumentException If the payment method is invalid
     */
    public function setPaymentMethod(string $method): self
    {
        if (!in_array($method, ['012', '022'])) {
            throw new InvalidArgumentException('Payment method must be either 012 (cash) or 022 (receivable)');
        }
        $this->paymentMethod = $method;
        return $this;
    }

    /**
     * Set an optional note or description for the invoice
     *
     * @param string $note The note or description
     * @return self
     */
    public function setNote(?string $note): self
    {
        $this->note = $note;
        return $this;
    }

    /**
     * Set the invoice counter (ICV)
     * This should be a sequential number starting from 1
     *
     * @param int $counter The invoice counter
     * @return self
     * @throws InvalidArgumentException If the counter is less than 1
     */
    public function setInvoiceCounter(int $counter): self
    {
        if ($counter < 1) {
            throw new InvalidArgumentException('Invoice counter must be greater than 0');
        }
        $this->invoiceCounter = $counter;
        return $this;
    }

    /**
     * Generate XML for this section
     *
     * @return string
     */
    public function generateXml(): string
    {
        // Will be implemented later
        return '';
    }

    /**
     * Get the current state of the invoice as an array
     * This is mainly used for testing purposes
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'invoiceId' => $this->invoiceId ?? null,
            'uuid' => $this->uuid ?? null,
            'issueDate' => isset($this->issueDate) ? $this->issueDate->format('d-m-Y') : null,
            'paymentMethod' => $this->paymentMethod,
            'note' => $this->note,
            'currency' => $this->currency,
            'invoiceCounter' => $this->invoiceCounter,
        ];
    }
}