<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\DTO\User;

use src\Modules\Identity\Domain\Enum\UserRoleEnum;

readonly class UpdateUserInputDTO
{
    public function __construct(
        public string $id,
        public ?string $name = null,
        public ?string $email = null,
        public ?string $password = null,
        public ?UserRoleEnum $role = null,
        public ?int $coordinationId = null,
        public bool $coordinationIdProvided = false,
    ) {}
}
