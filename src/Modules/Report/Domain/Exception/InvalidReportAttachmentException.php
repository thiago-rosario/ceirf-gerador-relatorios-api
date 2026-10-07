<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Exception;

use RuntimeException;
use src\Modules\Report\Domain\Enum\CodeExceptionEnum;
use Throwable;

class InvalidReportAttachmentException extends RuntimeException
{
    public function __construct(
        string $message = 'O anexo deve corresponder à sua categoria e possuir descrição de no máximo 500 caracteres.',
        int $code = CodeExceptionEnum::INVALID_REPORT_ATTACHMENT->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
