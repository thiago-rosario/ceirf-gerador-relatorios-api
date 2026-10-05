<?php

declare(strict_types=1);

namespace src\Identity\Application\DTO\Auth;

readonly class LogoutUserInputDTO
{
    public function __construct(
        public string $accessToken,
    ) {}
}
