<?php

declare(strict_types=1);

namespace src\Identity\Application\Interfaces\Adapter;

use src\Identity\Application\DTO\User\CreateUserInputDTO;
use src\Identity\Application\DTO\User\CreateUserOutputDTO;

interface CreateUserDataAdapterInterface
{
    /**
     * @param  array{name: string, email: string, password: string, role?: string}  $data
     */
    public function fromArray(array $data): CreateUserInputDTO;

    /**
     * @return array{id: string, name: string, email: string, role: string, is_active: bool, created_at: string}
     */
    public function toArray(CreateUserOutputDTO $data): array;
}
