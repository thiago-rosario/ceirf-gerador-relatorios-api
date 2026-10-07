<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\DTO\Auth;

readonly class AuthenticateUserInputDTO
{
    public function __construct(
        public string $email,
        public string $password,
    ) {}
}
