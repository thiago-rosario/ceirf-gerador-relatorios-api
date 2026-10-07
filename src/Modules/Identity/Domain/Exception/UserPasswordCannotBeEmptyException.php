<?php

declare(strict_types=1);

namespace src\Modules\Identity\Domain\Exception;

use RuntimeException;
use src\Modules\Identity\Domain\Enum\CodeExceptionEnum;
use Throwable;

class UserPasswordCannotBeEmptyException extends RuntimeException
{
    public function __construct(
        string $message = 'A senha do usuário não pode estar vazia.',
        int $code = CodeExceptionEnum::USER_EMPTY_PASSWORD->value,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
