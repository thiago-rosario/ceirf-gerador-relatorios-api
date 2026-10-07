<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Exception;

use RuntimeException;
use src\Modules\Report\Domain\Enum\CodeExceptionEnum;
use Throwable;

class DuplicateReportFileException extends RuntimeException
{
    public function __construct(
        string $message = 'O relatório não pode conter arquivos duplicados.',
        int $code = CodeExceptionEnum::DUPLICATE_REPORT_FILE->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
