<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Exception;

use RuntimeException;
use src\Modules\Report\Domain\Enum\CodeExceptionEnum;
use Throwable;

class InvalidMunicipalityException extends RuntimeException
{
    public function __construct(
        string $message = 'O município deve ser uma referência válida ao catálogo de municípios da Bahia.',
        int $code = CodeExceptionEnum::INVALID_MUNICIPALITY->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
