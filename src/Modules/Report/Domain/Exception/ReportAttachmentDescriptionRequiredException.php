<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Exception;

use RuntimeException;
use src\Modules\Report\Domain\Enum\CodeExceptionEnum;
use Throwable;

class ReportAttachmentDescriptionRequiredException extends RuntimeException
{
    public function __construct(
        string $message = 'Os arquivos em Outros devem possuir uma descrição para gerar o documento.',
        int $code = CodeExceptionEnum::REPORT_ATTACHMENT_DESCRIPTION_REQUIRED->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
