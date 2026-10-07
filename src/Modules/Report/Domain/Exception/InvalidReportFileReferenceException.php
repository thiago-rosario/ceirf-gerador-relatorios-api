<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Exception;

use RuntimeException;
use src\Modules\Report\Domain\Enum\CodeExceptionEnum;
use Throwable;

class InvalidReportFileReferenceException extends RuntimeException
{
    public function __construct(
        string $message = 'A referência do arquivo deve possuir identificador, nome, tipo, tamanho e hash válidos.',
        int $code = CodeExceptionEnum::INVALID_REPORT_FILE_REFERENCE->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
