<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\Enum;

enum CodeExceptionEnum: int
{
    case INVALID_CREDENTIALS_ERROR = 1101;

    case USER_NOT_FOUND_ERROR = 1102;

    case INVALID_USER_SEARCH_ERROR = 1103;
}
