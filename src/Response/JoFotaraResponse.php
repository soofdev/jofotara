<?php

namespace JBadarneh\JoFotara\Response;

/**
 * Handles responses from the JoFotara API
 */
class JoFotaraResponse
{
    /**
     * The raw response data from the API
     */
    private array $rawResponse;

    /**
     * Indicates if the response represents a successful submission
     */
    private bool $success;

    /**
     * The HTTP status code from the API response
     */
    private int $statusCode;

    /**
     * Create a new JoFotaraResponse instance
     *
     * @param  array  $response  The raw response from the API
     * @param  int  $statusCode  The HTTP status code from the API response
     */
    public function __construct(array $response, int $statusCode = 200)
    {
        $this->rawResponse = $response;
        $this->statusCode = $statusCode;

        // If status code is not 200, it's automatically not a success
        if ($statusCode !== 200) {
            $this->success = false;

            return;
        }

        // Determine if this is a success or error response based on structure
        if (isset($response['validationResults'])) {
            $this->success = $response['validationResults']['status'] === 'PASS' &&
                             ($response['invoiceStatus'] === 'SUBMITTED' || $response['invoiceStatus'] === 'ALREADY_SUBMITTED');
        } elseif (isset($response['EINV_RESULTS'])) {
            $this->success = $response['EINV_RESULTS']['status'] !== 'ERROR' &&
                             ($response['EINV_STATUS'] === 'SUBMITTED' || $response['EINV_STATUS'] === 'ALREADY_SUBMITTED');
        } else {
            $this->success = false;
        }
    }

    /**
     * Check if the invoice submission was successful
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * Get the raw response data
     */
    public function getRawResponse(): array
    {
        return $this->rawResponse;
    }

    /**
     * Get the invoice status (SUBMITTED, NOT_SUBMITTED, etc.)
     */
    public function getInvoiceStatus(): ?string
    {
        if (isset($this->rawResponse['invoiceStatus'])) {
            return $this->rawResponse['invoiceStatus'];
        }

        if (isset($this->rawResponse['EINV_STATUS'])) {
            return $this->rawResponse['EINV_STATUS'];
        }

        return null;
    }

    /**
     * Get the submitted invoice in base64 format
     */
    public function getSubmittedInvoice(): ?string
    {
        return $this->rawResponse['submittedInvoice'] ??
               $this->rawResponse['EINV_SINGED_INVOICE'] ??
               null;
    }

    /**
     * Get the submitted invoice decoded from base64 to XML
     */
    public function getInvoiceAsXml(): ?string
    {
        $base64Invoice = $this->getSubmittedInvoice();
        if ($base64Invoice === null) {
            return null;
        }

        $decoded = base64_decode($base64Invoice, true);
        if ($decoded === false) {
            return null;
        }

        return $decoded;
    }

    /**
     * Get the QR code for the invoice
     */
    public function getQrCode(): ?string
    {
        return $this->rawResponse['qrCode'] ??
               $this->rawResponse['EINV_QR'] ??
               null;
    }

    /**
     * Get the invoice number assigned by the system
     */
    public function getInvoiceNumber(): ?string
    {
        return $this->rawResponse['invoiceNumber'] ??
               $this->rawResponse['EINV_NUM'] ??
               null;
    }

    /**
     * Get the invoice UUID assigned by the system
     */
    public function getInvoiceUuid(): ?string
    {
        return $this->rawResponse['invoiceUUID'] ??
               $this->rawResponse['EINV_INV_UUID'] ??
               null;
    }

    /**
     * Get the HTTP status code from the API response
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get all error messages from the response
     */
    public function getErrors(): array
    {
        // Handle authentication errors (403)
        if ($this->statusCode === 403) {
            return [[
                'code' => $this->rawResponse['code'] ?? 'AUTH_ERROR',
                'message' => $this->rawResponse['error'] ?? 'Authentication failed. Please check your client ID and secret.',
                'category' => 'Authentication',
            ]];
        }

        if (isset($this->rawResponse['validationResults']['errorMessages'])) {
            return $this->rawResponse['validationResults']['errorMessages'];
        }

        if (isset($this->rawResponse['EINV_RESULTS']['ERRORS'])) {
            return $this->rawResponse['EINV_RESULTS']['ERRORS'];
        }

        // For error responses with status code 400 or specific error structure
        if (($this->statusCode === 400 || isset($this->rawResponse['error'])) && ! empty($this->rawResponse)) {
            // If there's a specific error structure, use it
            if (isset($this->rawResponse['error']) || isset($this->rawResponse['errors'])) {
                $error = isset($this->rawResponse['errors']) ? $this->rawResponse['errors'] : [[
                    'code' => $this->rawResponse['code'] ?? 'API_ERROR',
                    'message' => $this->rawResponse['error'] ?? json_encode($this->rawResponse),
                    'category' => 'API Validation',
                ]];

                return is_array($error) ? $error : [$error];
            }

            // If there's no specific error structure, create one from the response
            if (! isset($this->rawResponse['validationResults']) && ! isset($this->rawResponse['EINV_RESULTS'])) {
                return [[
                    'code' => 'API_ERROR',
                    'message' => json_encode($this->rawResponse),
                    'category' => 'API Validation',
                ]];
            }
        }

        return [];
    }

    /**
     * Get all warning messages from the response
     */
    public function getWarnings(): array
    {
        if (isset($this->rawResponse['validationResults']['warningMessages'])) {
            return $this->rawResponse['validationResults']['warningMessages'];
        }

        if (isset($this->rawResponse['EINV_RESULTS']['WARNINGS'])) {
            return $this->rawResponse['EINV_RESULTS']['WARNINGS'];
        }

        return [];
    }

    /**
     * Get all info messages from the response
     */
    public function getInfoMessages(): array
    {
        if (isset($this->rawResponse['validationResults']['infoMessages'])) {
            return $this->rawResponse['validationResults']['infoMessages'];
        }

        if (isset($this->rawResponse['EINV_RESULTS']['INFO'])) {
            return $this->rawResponse['EINV_RESULTS']['INFO'];
        }

        return [];
    }

    /**
     * Get the validation status (PASS, ERROR, etc.)
     */
    public function getValidationStatus(): ?string
    {
        if (isset($this->rawResponse['validationResults']['status'])) {
            return $this->rawResponse['validationResults']['status'];
        }

        if (isset($this->rawResponse['EINV_RESULTS']['status'])) {
            return $this->rawResponse['EINV_RESULTS']['status'];
        }

        return null;
    }

    /**
     * Check if there are any errors in the response
     */
    public function hasErrors(): bool
    {
        return count($this->getErrors()) > 0;
    }

    /**
     * Check if there are any warnings in the response
     */
    public function hasWarnings(): bool
    {
        return count($this->getWarnings()) > 0;
    }

    /**
     * Get a formatted summary of errors, if any
     */
    public function getErrorSummary(): ?string
    {
        $errors = $this->getErrors();
        if (empty($errors)) {
            return null;
        }

        $summary = [];
        foreach ($errors as $error) {
            $code = $error['code'] ?? $error['EINV_CODE'] ?? 'UNKNOWN';
            $message = $error['message'] ?? $error['EINV_MESSAGE'] ?? 'Unknown error';
            $category = $error['category'] ?? $error['EINV_CATEGORY'] ?? 'Unknown category';

            $summary[] = "[{$code}] {$category}: {$message}";
        }

        return implode("\n", $summary);
    }
}
