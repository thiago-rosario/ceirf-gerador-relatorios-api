<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Exception;

use RuntimeException;
use src\Modules\Report\Domain\Enum\CodeExceptionEnum;
use Throwable;

class InvalidReportRevisionException extends RuntimeException
{
    public function __construct(
        string $message = 'A identidade e a sequência da revisão documental devem ser consistentes.',
        int $code = CodeExceptionEnum::INVALID_REPORT_REVISION->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
