<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Exception;

use RuntimeException;
use src\Modules\Report\Domain\Enum\CodeExceptionEnum;
use Throwable;

class InvalidReportImageOrderException extends RuntimeException
{
    public function __construct(
        string $message = 'A ordem das imagens deve ser positiva, única e permanecer inalterada após o upload.',
        int $code = CodeExceptionEnum::INVALID_REPORT_IMAGE_ORDER->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
