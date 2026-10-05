<?php

declare(strict_types=1);

namespace App\Enum;

enum CodeExceptionEnum: int
{
    case JSEND_ERROR_MESSAGE_REQUIRED = 1201;

    case INVALID_JSEND_STATUS = 1202;

    case AUTHENTICATE_USER_INVALID_CREDENTIALS = 1203;
}
