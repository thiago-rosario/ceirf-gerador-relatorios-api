<?php

declare(strict_types=1);

namespace src\Modules\Identity\Domain\Entity;

use src\Modules\Identity\Domain\Enum\UserRoleEnum;

class RoleEntity
{
    public function __construct(
        private readonly int $id,
        private readonly string $code,
        private readonly string $name,
        private readonly UserRoleEnum $role,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function role(): UserRoleEnum
    {
        return $this->role;
    }
}
