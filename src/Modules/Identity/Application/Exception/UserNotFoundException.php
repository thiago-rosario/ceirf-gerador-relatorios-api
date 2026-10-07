<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\Exception;

use RuntimeException;
use src\Modules\Identity\Application\Enum\CodeExceptionEnum;
use Throwable;

class UserNotFoundException extends RuntimeException
{
    public function __construct(
        string $message = 'Usuário não encontrado.',
        int $code = CodeExceptionEnum::USER_NOT_FOUND_ERROR->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
