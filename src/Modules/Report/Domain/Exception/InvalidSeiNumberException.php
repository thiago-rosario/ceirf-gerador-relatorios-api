<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Exception;

use RuntimeException;
use src\Modules\Report\Domain\Enum\CodeExceptionEnum;
use Throwable;

class InvalidSeiNumberException extends RuntimeException
{
    public function __construct(
        string $message = 'O número SEI deve estar preenchido e possuir no máximo 80 caracteres.',
        int $code = CodeExceptionEnum::INVALID_SEI_NUMBER->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
