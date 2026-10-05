<?php

declare(strict_types=1);

namespace App\Exception;

use App\Enum\CodeExceptionEnum;
use RuntimeException;
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
