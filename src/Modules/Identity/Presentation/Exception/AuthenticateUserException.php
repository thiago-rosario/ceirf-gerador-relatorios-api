<?php

declare(strict_types=1);

namespace src\Modules\Identity\Presentation\Exception;

use RuntimeException;
use src\Modules\Identity\Presentation\Enum\CodeExceptionEnum;
use Throwable;

class AuthenticateUserException extends RuntimeException
{
    public function __construct(
        string $message = 'Credenciais inválidas.',
        int $code = CodeExceptionEnum::AUTHENTICATE_USER_INVALID_CREDENTIALS->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
