<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\DTO\User;

use DateTimeImmutable;
use src\Modules\Organization\Application\DTO\Coordination\CoordinationDataDTO;

readonly class CreateUserOutputDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public string $email,
        public string $role,
        public bool $isActive,
        public DateTimeImmutable $createdAt,
        public ?int $coordinationId = null,
        public ?CoordinationDataDTO $coordination = null,
    ) {}
}
