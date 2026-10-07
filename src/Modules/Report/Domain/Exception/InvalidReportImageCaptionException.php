<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Exception;

use RuntimeException;
use src\Modules\Report\Domain\Enum\CodeExceptionEnum;
use Throwable;

class InvalidReportImageCaptionException extends RuntimeException
{
    public function __construct(
        string $message = 'A legenda da imagem deve possuir no máximo 500 caracteres.',
        int $code = CodeExceptionEnum::INVALID_REPORT_IMAGE_CAPTION->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
