<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Exception;

use RuntimeException;
use src\Modules\Report\Domain\Enum\CodeExceptionEnum;
use Throwable;

class InvalidReportCollaboratorsException extends RuntimeException
{
    public function __construct(
        string $message = 'Os colaboradores devem possuir no máximo 1000 caracteres.',
        int $code = CodeExceptionEnum::INVALID_REPORT_COLLABORATORS->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
