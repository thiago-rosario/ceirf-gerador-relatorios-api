<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\Interfaces\Adapter;

use src\Modules\Identity\Application\DTO\User\ListAllUserInputDTO;
use src\Modules\Identity\Application\DTO\User\ListAllUserOutputDTO;

interface ListAllUserDataAdapterInterface
{
    /**
     * @param  array{filter?: string, order_by?: string}  $data
     */
    public function fromArray(array $data): ListAllUserInputDTO;

    /**
     * @return array{users: list<array{id: string, name: string, email: string, role: string, is_active: bool, created_at: string, must_change_password: bool, coordination_id: int|null, coordination: array{id: int, code: string, name: string}|null}>}
     */
    public function toArray(ListAllUserOutputDTO $data): array;
}
