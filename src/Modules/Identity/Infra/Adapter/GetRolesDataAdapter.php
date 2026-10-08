<?php

declare(strict_types=1);

namespace src\Modules\Identity\Infra\Adapter;

use src\Modules\Identity\Application\DTO\User\GetRolesOutputDTO;
use src\Modules\Identity\Application\DTO\User\RoleDataDTO;
use src\Modules\Identity\Application\Interfaces\Adapter\GetRolesDataAdapterInterface;

class GetRolesDataAdapter implements GetRolesDataAdapterInterface
{
    /**
     * @return array{roles: list<array{id: int, code: string, name: string, role: string}>}
     */
    public function toArray(GetRolesOutputDTO $data): array
    {
        return [
            'roles' => array_map(
                fn (RoleDataDTO $role): array => [
                    'id' => $role->id,
                    'code' => $role->code,
                    'name' => $role->name,
                    'role' => $role->role,
                ],
                $data->roles,
            ),
        ];
    }
}
