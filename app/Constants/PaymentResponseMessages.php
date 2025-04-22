<?php

namespace App\Constants;

class PaymentResponseMessages
{
    // General messages
    public const PROVIDER_NOT_FOUND = 'No query results for model [App\\Models\\PaymentProvider].';

    public const VALIDATION_ERROR = 'Validation error.';

    public const PAYMENT_FAILED = 'Payment processing failed: ';

    public const PAYMENT_PROCESSING_ERROR = 'Payment processing failed: Some error message.';

    public const PAYMENT_SUCCESS = 'Payment processed successfully';

    // Swagger-specific responses
    public const SWAGGER_SUCCESS_DESCRIPTION = self::PAYMENT_SUCCESS;

    public const SWAGGER_FAILED_DESCRIPTION = self::PAYMENT_PROCESSING_ERROR;

    public const SWAGGER_VALIDATION_DESCRIPTION = self::VALIDATION_ERROR;

    public const SWAGGER_NOT_FOUND_DESCRIPTION = 'Payment provider not found.';

    public const WEBHOOK_PROCESSED = 'Webhook procesado correctamente';

    public const TRANSACTION_ALREADY_PROCESSED = 'Transacción ya fue procesada previamente';

    public const TRANSACTION_NOT_FOUND = 'Transacción no encontrada';

    public const INVALID_DATA = 'Datos inválidos en el webhook';

    public const INVALID_TRANSACTION_ID = 'transaction_id inválido o faltante';

    public const INVALID_STATUS = 'status inválido o faltante';

    public const INTERNAL_ERROR = 'Error procesando el webhook';
}
