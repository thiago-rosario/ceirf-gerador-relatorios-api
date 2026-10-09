<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\Interfaces\Adapter;

use src\Modules\Identity\Application\DTO\User\UpdateUserInputDTO;
use src\Modules\Identity\Application\DTO\User\UpdateUserOutputDTO;

interface UpdateUserDataAdapterInterface
{
    /**
     * @param  array{id: string, name?: string, email?: string, password?: string, role?: string, coordination_id?: int|null}  $data
     */
    public function fromArray(array $data): UpdateUserInputDTO;

    /**
     * @return array{id: string, name: string, email: string, role: string, is_active: bool, created_at: string, updated_at: string, coordination_id: int|null, coordination: array{id: int, code: string, name: string}|null}
     */
    public function toArray(UpdateUserOutputDTO $data): array;
}
