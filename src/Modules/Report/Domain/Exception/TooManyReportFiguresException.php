<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Exception;

use RuntimeException;
use src\Modules\Report\Domain\Enum\CodeExceptionEnum;
use Throwable;

class TooManyReportFiguresException extends RuntimeException
{
    public function __construct(
        string $message = 'O relatório deve possuir no máximo 20 figuras, desconsiderando as imagens fixas da capa.',
        int $code = CodeExceptionEnum::TOO_MANY_REPORT_FIGURES->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
