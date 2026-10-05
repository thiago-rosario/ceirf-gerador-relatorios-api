<?php

declare(strict_types=1);

namespace src\Identity\Application\Interfaces\Adapter;

use src\Identity\Application\DTO\User\UpdateUserInputDTO;
use src\Identity\Application\DTO\User\UpdateUserOutputDTO;

interface UpdateUserDataAdapterInterface
{
    /**
     * @param  array{id: string, name?: string, email?: string, password?: string, role?: string}  $data
     */
    public function fromArray(array $data): UpdateUserInputDTO;

    /**
     * @return array{id: string, name: string, email: string, role: string, is_active: bool, created_at: string, updated_at: string}
     */
    public function toArray(UpdateUserOutputDTO $data): array;
}
