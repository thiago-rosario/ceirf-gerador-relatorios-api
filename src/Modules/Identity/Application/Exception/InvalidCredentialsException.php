<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\Exception;

use RuntimeException;
use src\Modules\Identity\Application\Enum\CodeExceptionEnum;
use Throwable;

class InvalidCredentialsException extends RuntimeException
{
    public function __construct(
        string $message = 'Credenciais inválidas.',
        int $code = CodeExceptionEnum::INVALID_CREDENTIALS_ERROR->value,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
