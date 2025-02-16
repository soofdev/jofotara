<?php

namespace JBadarneh\JoFotara\Tests\Helpers;

use DOMDocument;
use LibXMLError;

trait XmlSchemaValidator
{
    /**
     * Validate XML string against UBL 2.1 Invoice schema
     *
     * @param string $xml The XML string to validate
     * @return array{isValid: bool, errors: LibXMLError[]} Validation result and any errors
     */
    protected function validateAgainstUblSchema(string $xml): array
    {
        // Enable internal error handling and set maximum error level
        $originalErrorReporting = libxml_use_internal_errors(true);
        $originalUseErrors = libxml_use_internal_errors(true);

        // Get absolute paths for schema directories
        $mainSchemaDir = realpath(__DIR__ . '/../Fixtures');
        $commonSchemaDir = realpath(__DIR__ . '/../common');

        // Read and modify the main schema
        $mainSchemaPath = $mainSchemaDir . '/UBL-Invoice-2.1.xsd';
        $mainSchemaContent = file_get_contents($mainSchemaPath);

        // Replace relative paths with absolute paths in the schema
        $mainSchemaContent = str_replace(
            '../common/',
            $commonSchemaDir . '/',
            $mainSchemaContent
        );

        // Save modified schema to temporary file
        $tempSchemaFile = tempnam(sys_get_temp_dir(), 'schema');
        file_put_contents($tempSchemaFile, $mainSchemaContent);

        // Create a new DOM document with validation
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        // Load and validate XML
        $dom->loadXML($xml);

        // Validate against schema
        $isValid = $dom->schemaValidate($tempSchemaFile);

        // Get any validation errors
        $errors = libxml_get_errors();
        libxml_clear_errors();

        // Clean up
        unlink($tempSchemaFile);
        libxml_use_internal_errors($originalUseErrors);

        return [
            'isValid' => $isValid,
            'errors' => $errors
        ];
    }

    /**
     * Format LibXML errors into readable messages
     *
     * @param LibXMLError[] $errors Array of LibXMLError objects
     * @return string Formatted error message
     */
    protected function formatSchemaErrors(array $errors): string
    {
        $messages = [];
        foreach ($errors as $error) {
            $messages[] = sprintf(
                "[%s] %s (Line: %d, Column: %d)",
                $this->getErrorLevel($error->level),
                trim($error->message),
                $error->line,
                $error->column
            );
        }
        return implode("\n", $messages);
    }

    /**
     * Get human-readable error level
     *
     * @param int $level LibXML error level
     * @return string Human-readable error level
     */
    private function getErrorLevel(int $level): string
    {
        return match ($level) {
            LIBXML_ERR_WARNING => 'Warning',
            LIBXML_ERR_ERROR => 'Error',
            LIBXML_ERR_FATAL => 'Fatal Error',
            default => 'Unknown'
        };
    }
}
