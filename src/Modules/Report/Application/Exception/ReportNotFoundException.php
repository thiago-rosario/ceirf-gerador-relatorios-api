<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\Exception;

use RuntimeException;
use src\Modules\Report\Application\Enum\CodeExceptionEnum;
use Throwable;

class ReportNotFoundException extends RuntimeException
{
    public function __construct(
        string $message = 'Relatório não encontrado.',
        int $code = CodeExceptionEnum::ReportNotFoundError->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
