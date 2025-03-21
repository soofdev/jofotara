<?php

namespace JBadarneh\JoFotara\Sections;

use InvalidArgumentException;
use JBadarneh\JoFotara\Contracts\ValidatableSection;
use JBadarneh\JoFotara\Traits\XmlHelperTrait;

class InvoiceItems implements ValidatableSection
{
    use XmlHelperTrait;

    private array $items = [];

    /**
     * Add a new line item to the invoice
     *
     * @param  string  $id  Unique serial number for this line item
     */
    public function addItem(string $id): InvoiceLineItem
    {
        if (isset($this->items[$id])) {
            throw new InvalidArgumentException("Item with ID {$id} already exists");
        }

        $item = new InvoiceLineItem($id);
        $this->items[$id] = $item;

        return $item;
    }

    /**
     * Get all line items
     *
     * @return array<string, InvoiceLineItem>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Convert all invoice items to XML
     *
     * @return string The XML representation
     */
    public function toXml(): string
    {
        if (empty($this->items)) {
            throw new InvalidArgumentException('At least one invoice item is required');
        }

        $xml = [];
        foreach ($this->items as $item) {
            $xml[] = $item->toXml();
        }

        return implode("\n", $xml);
    }

    /**
     * Get the current state as an array
     * This is mainly used for testing purposes
     */
    public function toArray(): array
    {
        $items = [];
        foreach ($this->items as $id => $item) {
            $items[$id] = $item->toArray();
        }

        return $items;
    }

    /**
     * Validate that all required fields are set and valid
     *
     * @throws InvalidArgumentException If validation fails
     */
    public function validateSection(): void
    {
        if (empty($this->items)) {
            throw new InvalidArgumentException('At least one invoice item is required');
        }

        // Validate each item
        foreach ($this->items as $item) {
            $item->validateSection();
        }
    }
}
