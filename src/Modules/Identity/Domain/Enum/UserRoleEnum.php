<?php

declare(strict_types=1);

namespace src\Modules\Identity\Domain\Enum;

enum UserRoleEnum: string
{
    case OPERATOR = 'OPERATOR';

    case VIEWER = 'VIEWER';

    case REVIEWER = 'REVIEWER';

    case SUPERUSER = 'SUPERUSER';
}
