<?php

declare(strict_types=1);

namespace src\Identity\Domain\Exception;

use RuntimeException;
use src\Identity\Domain\Enum\CodeExceptionEnum;
use Throwable;

class InvalidUserIdException extends RuntimeException
{
    public function __construct(
        string $className,
        string|int $id,
        int $code = CodeExceptionEnum::INVALID_USER_ID->value,
        ?Throwable $previous = null
    ) {
        parent::__construct(
            sprintf(
                'A classe <%s> não permite o valor <%s>.',
                $className,
                $id
            ),
            $code,
            $previous
        );
    }
}
