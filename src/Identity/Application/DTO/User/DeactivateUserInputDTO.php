<?php

declare(strict_types=1);

namespace src\Identity\Application\DTO\User;

readonly class DeactivateUserInputDTO
{
    public function __construct(
        public string $id
    ) {}
}
