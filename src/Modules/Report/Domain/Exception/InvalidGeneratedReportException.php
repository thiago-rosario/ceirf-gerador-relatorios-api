<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Exception;

use RuntimeException;
use src\Modules\Report\Domain\Enum\CodeExceptionEnum;
use Throwable;

class InvalidGeneratedReportException extends RuntimeException
{
    public function __construct(
        string $message = 'O documento gerado deve possuir uma referência válida e o nome PDF correspondente ao relatório.',
        int $code = CodeExceptionEnum::INVALID_GENERATED_REPORT->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
