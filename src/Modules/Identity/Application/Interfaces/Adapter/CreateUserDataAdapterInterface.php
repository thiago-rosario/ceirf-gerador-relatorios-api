<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\Interfaces\Adapter;

use src\Modules\Identity\Application\DTO\User\CreateUserInputDTO;
use src\Modules\Identity\Application\DTO\User\CreateUserOutputDTO;

interface CreateUserDataAdapterInterface
{
    /**
     * @param  array{name: string, email: string, password: string, role?: string, coordination_id?: int|null}  $data
     */
    public function fromArray(array $data): CreateUserInputDTO;

    /**
     * @return array{id: string, name: string, email: string, role: string, is_active: bool, created_at: string, coordination_id: int|null, coordination: array{id: int, code: string, name: string}|null}
     */
    public function toArray(CreateUserOutputDTO $data): array;
}
