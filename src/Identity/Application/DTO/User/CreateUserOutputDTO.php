<?php

declare(strict_types=1);

namespace src\Identity\Application\DTO\User;

use DateTimeImmutable;

readonly class CreateUserOutputDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public string $email,
        public string $role,
        public bool $isActive,
        public DateTimeImmutable $createdAt,
    ) {}
}
