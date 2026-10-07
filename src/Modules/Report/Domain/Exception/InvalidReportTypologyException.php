<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Exception;

use RuntimeException;
use src\Modules\Report\Domain\Enum\CodeExceptionEnum;
use Throwable;

class InvalidReportTypologyException extends RuntimeException
{
    public function __construct(
        string $message = 'A tipologia deve possuir no máximo 100 caracteres.',
        int $code = CodeExceptionEnum::INVALID_REPORT_TYPOLOGY->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
