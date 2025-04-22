<?php

namespace App\Constants;

class PaymentMessages
{
    public const MISSING_FIELD = 'Falta el campo requerido: %s';

    public const INVALID_AMOUNT = 'El monto debe ser un número entero (no decimal).';

    public const REQUEST_FAILED = 'EasyMoney payment failed: ';

    public const INVALID_RESPONSE = 'EasyMoney payment response is invalid.';

    public const INVALID_STATUS = 'EasyMoney payment status is invalid.';

    public const TRANSACTION_NOT_FOUND = 'Transacción no encontrada.';

    public const TRANSACTION_ALREADY_PROCESSED = 'Transacción ya procesada.';
}
