<?php

declare(strict_types=1);

namespace src\Identity\Application\Enum;

enum CodeExceptionEnum: int
{
    case INVALID_CREDENTIALS_ERROR = 1101;
}
