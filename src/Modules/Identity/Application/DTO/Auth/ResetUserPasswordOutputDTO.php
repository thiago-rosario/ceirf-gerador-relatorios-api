<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\DTO\Auth;

readonly class ResetUserPasswordOutputDTO
{
    public function __construct(
        public string $id,
        public bool $mustChangePassword,
    ) {}
}
