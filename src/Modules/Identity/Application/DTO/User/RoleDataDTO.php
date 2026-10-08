<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\DTO\User;

readonly class RoleDataDTO
{
    public function __construct(
        public int $id,
        public string $code,
        public string $name,
        public string $role,
    ) {}
}
