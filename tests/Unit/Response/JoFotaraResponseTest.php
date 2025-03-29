<?php

use JBadarneh\JoFotara\Response\JoFotaraResponse;

test('it correctly identifies a successful response', function () {
    $successResponse = [
        'validationResults' => [
            'infoMessages' => [
                [
                    'type' => 'INFO',
                    'code' => 'XSD_VALID',
                    'category' => 'XSD validation',
                    'message' => 'Complied with UBL 2.1 standards in line with ZATCA specifications',
                    'status' => 'PASS',
                ],
            ],
            'warningMessages' => [],
            'errorMessages' => [],
            'status' => 'PASS',
        ],
        'invoiceStatus' => 'SUBMITTED',
        'submittedInvoice' => 'base64encodedcontent',
        'qrCode' => 'qrcodedata',
        'invoiceNumber' => 'EIN00032',
        'invoiceUUID' => '6c13d4a6-8fa9-46b4-b9e0-fb74b7c3f44a',
    ];

    $response = new JoFotaraResponse($successResponse, 200);

    expect($response->isSuccess())->toBeTrue()
        ->and($response->getStatusCode())->toBe(200)
        ->and($response->getInvoiceStatus())->toBe('SUBMITTED')
        ->and($response->getSubmittedInvoice())->toBe('base64encodedcontent')
        ->and($response->getQrCode())->toBe('qrcodedata')
        ->and($response->getInvoiceNumber())->toBe('EIN00032')
        ->and($response->getInvoiceUuid())->toBe('6c13d4a6-8fa9-46b4-b9e0-fb74b7c3f44a')
        ->and($response->getValidationStatus())->toBe('PASS')
        ->and($response->hasErrors())->toBeFalse()
        ->and($response->hasWarnings())->toBeFalse()
        ->and($response->getErrorSummary())->toBeNull();
});

test('it correctly identifies an error response', function () {
    $errorResponse = [
        'EINV_RESULTS' => [
            'status' => 'ERROR',
            'INFO' => [],
            'WARNINGS' => [],
            'ERRORS' => [
                [
                    'type' => 'ERROR',
                    'status' => 'ERROR',
                    'EINV_CODE' => 'XSD_INVALID',
                    'EINV_CATEGORY' => 'XSD validation',
                    'EINV_MESSAGE' => 'Schema validation failed; XML does not comply with UBL 2.1 standards',
                ],
            ],
        ],
        'EINV_STATUS' => 'NOT_SUBMITTED',
        'EINV_SINGED_INVOICE' => null,
        'EINV_QR' => null,
        'EINV_NUM' => null,
        'EINV_INV_UUID' => null,
    ];

    $response = new JoFotaraResponse($errorResponse, 200);

    expect($response->isSuccess())->toBeFalse()
        ->and($response->getStatusCode())->toBe(200)
        ->and($response->getInvoiceStatus())->toBe('NOT_SUBMITTED')
        ->and($response->getSubmittedInvoice())->toBeNull()
        ->and($response->getQrCode())->toBeNull()
        ->and($response->getInvoiceNumber())->toBeNull()
        ->and($response->getInvoiceUuid())->toBeNull()
        ->and($response->getValidationStatus())->toBe('ERROR')
        ->and($response->hasErrors())->toBeTrue()
        ->and($response->hasWarnings())->toBeFalse()
        ->and($response->getErrorSummary())->toContain('XSD_INVALID')
        ->and($response->getErrorSummary())->toContain('XSD validation')
        ->and($response->getErrorSummary())->toContain('Schema validation failed');
});

test('it provides access to raw response data', function () {
    $responseData = [
        'validationResults' => [
            'status' => 'PASS',
        ],
        'invoiceStatus' => 'SUBMITTED',
    ];

    $response = new JoFotaraResponse($responseData, 200);

    expect($response->getRawResponse())->toBe($responseData)
        ->and($response->getStatusCode())->toBe(200);
});

test('it handles info messages correctly', function () {
    $responseData = [
        'validationResults' => [
            'infoMessages' => [
                [
                    'type' => 'INFO',
                    'code' => 'INFO_CODE',
                    'message' => 'Information message',
                ],
            ],
            'status' => 'PASS',
        ],
        'invoiceStatus' => 'SUBMITTED',
    ];

    $response = new JoFotaraResponse($responseData, 200);

    expect($response->getInfoMessages())->toHaveCount(1)
        ->and($response->getInfoMessages()[0]['code'])->toBe('INFO_CODE');
});

test('it handles warning messages correctly', function () {
    $responseData = [
        'validationResults' => [
            'warningMessages' => [
                [
                    'type' => 'WARNING',
                    'code' => 'WARNING_CODE',
                    'message' => 'Warning message',
                ],
            ],
            'status' => 'PASS',
        ],
        'invoiceStatus' => 'SUBMITTED',
    ];

    $response = new JoFotaraResponse($responseData, 200);

    expect($response->getWarnings())->toHaveCount(1)
        ->and($response->hasWarnings())->toBeTrue()
        ->and($response->getWarnings()[0]['code'])->toBe('WARNING_CODE');
});

test('it handles alternative response format correctly', function () {
    $alternativeResponse = [
        'EINV_RESULTS' => [
            'status' => 'PASS',
            'INFO' => [
                [
                    'type' => 'INFO',
                    'EINV_CODE' => 'INFO_CODE',
                    'EINV_MESSAGE' => 'Information message',
                ],
            ],
            'WARNINGS' => [],
            'ERRORS' => [],
        ],
        'EINV_STATUS' => 'SUBMITTED',
        'EINV_SINGED_INVOICE' => 'base64content',
        'EINV_QR' => 'qrdata',
        'EINV_NUM' => 'EIN12345',
        'EINV_INV_UUID' => 'uuid-string',
    ];

    $response = new JoFotaraResponse($alternativeResponse, 200);

    expect($response->isSuccess())->toBeTrue()
        ->and($response->getStatusCode())->toBe(200)
        ->and($response->getInvoiceStatus())->toBe('SUBMITTED')
        ->and($response->getSubmittedInvoice())->toBe('base64content')
        ->and($response->getQrCode())->toBe('qrdata')
        ->and($response->getInvoiceNumber())->toBe('EIN12345')
        ->and($response->getInvoiceUuid())->toBe('uuid-string')
        ->and($response->getInfoMessages())->toHaveCount(1);
});

test('it handles HTTP 400 error responses', function () {
    $errorResponse = [
        'error' => [
            'code' => 'VALIDATION_ERROR',
            'message' => 'Invoice validation failed',
            'details' => 'Missing required fields',
        ],
    ];

    $response = new JoFotaraResponse($errorResponse, 400);

    expect($response->isSuccess())->toBeFalse()
        ->and($response->getStatusCode())->toBe(400)
        ->and($response->hasErrors())->toBeTrue()
        ->and($response->getErrors())->toHaveCount(1);
});

test('it creates a generic error for unstructured 400 responses', function () {
    $errorResponse = [
        'message' => 'Something went wrong',
        'timestamp' => '2025-03-29T18:16:23+03:00',
    ];

    $response = new JoFotaraResponse($errorResponse, 400);

    expect($response->isSuccess())->toBeFalse()
        ->and($response->getStatusCode())->toBe(400)
        ->and($response->hasErrors())->toBeTrue()
        ->and($response->getErrors()[0]['code'])->toBe('API_ERROR')
        ->and($response->getErrors()[0]['category'])->toBe('API Validation');
});

test('it treats ALREADY_SUBMITTED status as success', function () {
    $alreadySubmittedResponse = [
        'EINV_RESULTS' => [
            'status' => 'PASS',
            'INFO' => [
                [
                    'type' => 'INFO',
                    'status' => 'PASS',
                    'EINV_CODE' => 'XSD_VALID',
                    'EINV_CATEGORY' => 'XSD validation',
                    'EINV_MESSAGE' => 'Complied with UBL 2.1 standards',
                ],
            ],
            'WARNINGS' => [],
            'ERRORS' => [],
        ],
        'EINV_STATUS' => 'ALREADY_SUBMITTED',
        'EINV_SINGED_INVOICE' => 'base64content',
        'EINV_QR' => 'qrcodedata',
        'EINV_NUM' => '002',
        'EINV_INV_UUID' => '123e4567-e89b-12d3-a456-111111111112',
    ];

    $response = new JoFotaraResponse($alreadySubmittedResponse, 200);

    expect($response->isSuccess())->toBeTrue()
        ->and($response->getStatusCode())->toBe(200)
        ->and($response->getInvoiceStatus())->toBe('ALREADY_SUBMITTED')
        ->and($response->getSubmittedInvoice())->toBe('base64content')
        ->and($response->getQrCode())->toBe('qrcodedata')
        ->and($response->getInvoiceNumber())->toBe('002')
        ->and($response->getInvoiceUuid())->toBe('123e4567-e89b-12d3-a456-111111111112');
});
