<?php

declare(strict_types=1);

namespace src\Identity\Application\DTO\User;

use src\Identity\Domain\Enum\UserRoleEnum;

readonly class CreateUserInputDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public UserRoleEnum $role = UserRoleEnum::OPERATOR,
    ) {}
}
