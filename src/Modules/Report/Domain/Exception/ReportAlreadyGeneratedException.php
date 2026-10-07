<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Exception;

use RuntimeException;
use src\Modules\Report\Domain\Enum\CodeExceptionEnum;
use Throwable;

class ReportAlreadyGeneratedException extends RuntimeException
{
    public function __construct(
        string $message = 'Um relatório já gerado não pode ser alterado. Crie uma nova revisão.',
        int $code = CodeExceptionEnum::REPORT_ALREADY_GENERATED->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
