<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\Interfaces\Adapter;

use src\Modules\Identity\Application\DTO\User\GetRolesOutputDTO;

interface GetRolesDataAdapterInterface
{
    /**
     * @return array{roles: list<array{id: int, code: string, name: string, role: string}>}
     */
    public function toArray(GetRolesOutputDTO $data): array;
}
