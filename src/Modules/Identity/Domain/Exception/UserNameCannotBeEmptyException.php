<?php

declare(strict_types=1);

namespace src\Modules\Identity\Domain\Exception;

use RuntimeException;
use src\Modules\Identity\Domain\Enum\CodeExceptionEnum;
use Throwable;

class UserNameCannotBeEmptyException extends RuntimeException
{
    public function __construct(
        string $message = 'O nome do usuário não pode estar vazio.',
        int $code = CodeExceptionEnum::USER_EMPTY_NAME->value,
        ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
