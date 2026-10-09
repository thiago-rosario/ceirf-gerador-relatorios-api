<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\Exception;

use RuntimeException;

class PasswordChangeRejectedException extends RuntimeException
{
    public function __construct(
        public readonly string $field,
        string $message,
    ) {
        parent::__construct($message, 422);
    }
}
