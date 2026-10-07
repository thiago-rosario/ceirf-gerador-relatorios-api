<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\DTO\Auth;

readonly class LogoutUserInputDTO
{
    public function __construct(
        public string $accessToken,
    ) {}
}
