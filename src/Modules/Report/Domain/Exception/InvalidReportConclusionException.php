<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Exception;

use RuntimeException;
use src\Modules\Report\Domain\Enum\CodeExceptionEnum;
use Throwable;

class InvalidReportConclusionException extends RuntimeException
{
    public function __construct(
        string $message = 'A conclusão deve estar preenchida para gerar o documento.',
        int $code = CodeExceptionEnum::INVALID_REPORT_CONCLUSION->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
