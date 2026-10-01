<?php

declare(strict_types=1);

namespace src\Identity\Application\Exception;

use RuntimeException;
use src\Identity\Application\Enum\CodeExceptionEnum;
use Throwable;

class InvalidCredentialsException extends RuntimeException
{
    public function __construct(
        string $message = 'Credenciais inválidas.',
        int $code = CodeExceptionEnum::INVALID_CREDENTIALS_ERROR->vINVALID_CREDENTIALS_ERRORalue,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
