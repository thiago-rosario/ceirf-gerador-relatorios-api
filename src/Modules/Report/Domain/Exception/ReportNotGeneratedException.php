<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Exception;

use RuntimeException;
use src\Modules\Report\Domain\Enum\CodeExceptionEnum;
use Throwable;

class ReportNotGeneratedException extends RuntimeException
{
    public function __construct(
        string $message = 'Uma revisão documental somente pode ser criada a partir de um relatório gerado.',
        int $code = CodeExceptionEnum::REPORT_NOT_GENERATED->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
