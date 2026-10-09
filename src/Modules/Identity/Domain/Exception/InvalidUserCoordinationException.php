<?php

declare(strict_types=1);

namespace src\Modules\Identity\Domain\Exception;

use RuntimeException;
use src\Modules\Identity\Domain\Enum\CodeExceptionEnum;
use Throwable;

class InvalidUserCoordinationException extends RuntimeException
{
    public function __construct(
        string $message = 'A coordenação informada é inválida.',
        int $code = CodeExceptionEnum::INVALID_USER_COORDINATION->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
