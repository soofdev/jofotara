<?php

namespace JBadarneh\JoFotara\Sections;

use InvalidArgumentException;
use JBadarneh\JoFotara\Traits\XmlHelperTrait;

class SellerSupplierParty
{
    use XmlHelperTrait;

    private ?string $sequenceId = null;

    /**
     * Set the seller's sequence of income source (activity)
     *
     * @param  string  $id  The sequence ID
     */
    public function setSequenceId(string $id): self
    {
        $this->sequenceId = $id;

        return $this;
    }

    /**
     * Convert the seller supplier party information to XML
     *
     * @return string The XML representation
     */
    public function toXml(): string
    {
        if ($this->sequenceId === null) {
            throw new InvalidArgumentException('Sequence of income source is required');
        }

        $xml = [];
        $xml[] = '<cac:SellerSupplierParty>';
        $xml[] = '    <cac:Party>';
        $xml[] = '        <cac:PartyIdentification>';
        $xml[] = sprintf('            <cbc:ID>%s</cbc:ID>', $this->escapeXml($this->sequenceId));
        $xml[] = '        </cac:PartyIdentification>';
        $xml[] = '    </cac:Party>';
        $xml[] = '</cac:SellerSupplierParty>';

        return implode("\n", $xml);
    }

    /**
     * Get the current state as an array
     * This is mainly used for testing purposes
     */
    public function toArray(): array
    {
        return [
            'sequenceId' => $this->sequenceId,
        ];
    }
}
