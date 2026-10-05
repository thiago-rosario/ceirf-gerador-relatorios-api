<?php

declare(strict_types=1);

namespace src\Identity\Application\DTO\Auth;

readonly class ResetUserPasswordInputDTO
{
    public function __construct(
        public string $id,
    ) {}
}
