<?php

declare(strict_types=1);

namespace src\Identity\Application\DTO\User;

use src\Identity\Domain\Enum\UserRoleEnum;

readonly class UpdateUserInputDTO
{
    public function __construct(
        public string $id,
        public ?string $name = null,
        public ?string $email = null,
        public ?string $password = null,
        public ?UserRoleEnum $role = null,
    ) {}
}
