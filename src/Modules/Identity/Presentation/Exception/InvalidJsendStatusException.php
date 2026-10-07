<?php

declare(strict_types=1);

namespace src\Modules\Identity\Presentation\Exception;

use RuntimeException;
use src\Modules\Identity\Presentation\Enum\CodeExceptionEnum;
use Throwable;

class InvalidJsendStatusException extends RuntimeException
{
    public function __construct(
        string $status,
        int $code = CodeExceptionEnum::INVALID_JSEND_STATUS->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct("Invalid JSend status [{$status}].", $code, $previous);
    }
}
