<?php

declare(strict_types=1);

namespace src\Identity\Domain\Exception;

use RuntimeException;
use src\Identity\Domain\Enum\CodeExceptionEnum;
use Throwable;

class InvalidEmailException extends RuntimeException
{
    public function __construct(
        string $message = 'O e-mail deve possuir uma estrutura válida.',
        int $code = CodeExceptionEnum::INVALID_EMAIL->value,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
