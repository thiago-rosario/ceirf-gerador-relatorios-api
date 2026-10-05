<?php

declare(strict_types=1);

namespace App\Exception;

use App\Enum\CodeExceptionEnum;
use RuntimeException;
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
