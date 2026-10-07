<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Exception;

use RuntimeException;
use src\Modules\Report\Domain\Enum\CodeExceptionEnum;
use Throwable;

class InvalidReportIdException extends RuntimeException
{
    public function __construct(
        string $message = 'O identificador do relatório deve ser um UUID válido.',
        int $code = CodeExceptionEnum::INVALID_REPORT_ID->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
