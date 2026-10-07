<?php

declare(strict_types=1);

namespace src\Modules\Identity\Presentation\Exception;

use RuntimeException;
use src\Modules\Identity\Presentation\Enum\CodeExceptionEnum;
use Throwable;

class JsendErrorMessageRequiredException extends RuntimeException
{
    public function __construct(
        string $message = 'JSend error responses require a message.',
        int $code = CodeExceptionEnum::JSEND_ERROR_MESSAGE_REQUIRED->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
