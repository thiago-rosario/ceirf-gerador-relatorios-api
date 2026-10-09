<?php

declare(strict_types=1);

namespace src\Modules\Identity\Domain\Enum;

enum CodeExceptionEnum: int
{
    case USER_EMPTY_NAME = 1001;

    case USER_EMPTY_PASSWORD = 1002;

    case INVALID_USER_ID = 1003;

    case INVALID_EMAIL = 1004;

    case INVALID_USER_COORDINATION = 1005;
}
