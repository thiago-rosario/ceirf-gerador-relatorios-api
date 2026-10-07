<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Exception;

use RuntimeException;
use src\Modules\Report\Domain\Enum\CodeExceptionEnum;
use Throwable;

class FutureInspectionDateException extends RuntimeException
{
    public function __construct(
        string $message = 'A data da vistoria não pode ser futura em relação ao momento da edição.',
        int $code = CodeExceptionEnum::FUTURE_INSPECTION_DATE->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
