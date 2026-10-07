<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Exception;

use RuntimeException;
use src\Modules\Report\Domain\Enum\CodeExceptionEnum;
use Throwable;

class InvalidReportStatusException extends RuntimeException
{
    public function __construct(
        string $message = 'O estado do relatório deve corresponder à presença do documento gerado.',
        int $code = CodeExceptionEnum::INVALID_REPORT_STATUS->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
