<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Exception;

use RuntimeException;
use src\Modules\Report\Domain\Enum\CodeExceptionEnum;
use Throwable;

class InvalidReportImageFormatException extends RuntimeException
{
    public function __construct(
        string $message = 'As imagens do relatório devem estar nos formatos PNG, JPEG, JPG ou PDF.',
        int $code = CodeExceptionEnum::INVALID_REPORT_IMAGE_FORMAT->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
