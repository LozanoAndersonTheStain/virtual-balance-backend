<?php

namespace VirtualBalance\Shared\Constants;

class AppConstants
{
    // Transaction Types
    public const TRANSACTION_TYPE_RECHARGE = 'RECHARGE';
    public const TRANSACTION_TYPE_PAYMENT = 'PAYMENT';

    // Transaction Status
    public const TRANSACTION_STATUS_PENDING = 'PENDING';
    public const TRANSACTION_STATUS_COMPLETED = 'COMPLETED';
    public const TRANSACTION_STATUS_FAILED = 'FAILED';

    // Limits
    public const MAX_RECHARGE_AMOUNT = 1000000; // 1 millón
    public const MAX_PAYMENT_AMOUNT = 500000;   // 500 mil
    public const MIN_TRANSACTION_AMOUNT = 1;

    // Response Codes
    public const HTTP_OK = 200;
    public const HTTP_CREATED = 201;
    public const HTTP_BAD_REQUEST = 400;
    public const HTTP_UNAUTHORIZED = 401;
    public const HTTP_NOT_FOUND = 404;
    public const HTTP_CONFLICT = 409;
    public const HTTP_UNPROCESSABLE = 422;
    public const HTTP_INTERNAL_ERROR = 500;

    // Messages
    public const MSG_SUCCESS = 'Operación exitosa';
    public const MSG_ERROR = 'Ha ocurrido un error';
    public const MSG_VALIDATION_ERROR = 'Error de validación';
    public const MSG_NOT_FOUND = 'Recurso no encontrado';
    public const MSG_UNAUTHORIZED = 'No autorizado';
}
