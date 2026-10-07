<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Exception;

use RuntimeException;
use src\Modules\Report\Domain\Enum\CodeExceptionEnum;
use Throwable;

class IncompleteReportException extends RuntimeException
{
    public function __construct(
        string $message = 'O relatório deve estar completo para gerar o documento.',
        int $code = CodeExceptionEnum::INCOMPLETE_REPORT->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
