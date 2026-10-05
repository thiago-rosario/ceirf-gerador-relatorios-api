<?php

declare(strict_types=1);

namespace src\Identity\Application\DTO\User;

readonly class UserDataDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public string $email,
        public string $role,
        public bool $mustChangePassword = false,
    ) {}
}
