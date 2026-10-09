<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\DTO\Auth;

readonly class ChangeUserPasswordInputDTO
{
    public function __construct(
        public string $id,
        public string $currentPassword,
        public string $password,
        public string $accessToken,
    ) {}
}
