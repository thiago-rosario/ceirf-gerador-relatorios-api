<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Exception;

use RuntimeException;
use src\Modules\Report\Domain\Enum\CodeExceptionEnum;
use Throwable;

class InvalidReportDateException extends RuntimeException
{
    public function __construct(
        string $message = 'A data do relatório deve possuir uma estrutura válida.',
        int $code = CodeExceptionEnum::INVALID_REPORT_DATE->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
