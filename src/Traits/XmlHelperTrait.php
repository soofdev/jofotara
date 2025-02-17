<?php

namespace JBadarneh\JoFotara\Traits;

trait XmlHelperTrait
{
    /**
     * Escape special characters for XML output
     *
     * @param  string|null  $value  The value to escape
     * @return string The escaped value
     */
    private function escapeXml(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    /**
     * Normalize XML line endings to Unix style (LF)
     *
     * @param  string  $xml  The XML string to normalize
     * @return string The normalized XML string
     */
    private function normalizeXml(string $xml): string
    {
        return str_replace("\r\n", "\n", $xml);
    }
}
