<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\DTO\User;

use src\Modules\Identity\Domain\Enum\UserRoleEnum;

readonly class CreateUserInputDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public UserRoleEnum $role = UserRoleEnum::OPERATOR,
    ) {}
}
