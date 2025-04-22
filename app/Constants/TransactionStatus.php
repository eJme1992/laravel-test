<?php

namespace App\Constants;

class TransactionStatus
{
    // Definición de los posibles estados de una transacción como constantes
    public const PENDING = 'pending';    // Transacción pendiente

    public const SUCCESS = 'success';    // Transacción exitosa

    public const FAILED = 'failed';      // Transacción fallida

    public const CANCELED = 'canceled';  // Transacción cancelada

    public const REFUNDED = 'refunded';  // Transacción reembolsada
}
