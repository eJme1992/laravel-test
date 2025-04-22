<?php

namespace App\Constants;

class ResponseStatus
{
    // Estados de la transacción
    public const PENDING = 'pending';

    public const SUCCESS = 'success';

    public const FAILED = 'failed';

    // Códigos de respuesta HTTP
    public const HTTP_OK = 200;

    public const HTTP_BAD_REQUEST = 400;

    public const HTTP_INTERNAL_ERROR = 500;

    // Otros códigos que podrías agregar si fueran necesarios:
    // public const UNAUTHORIZED = 401;
    // public const FORBIDDEN = 403;
    // public const NOT_FOUND = 404;
}
