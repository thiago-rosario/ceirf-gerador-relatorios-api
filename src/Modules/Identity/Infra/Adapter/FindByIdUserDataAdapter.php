<?php

declare(strict_types=1);

namespace src\Modules\Identity\Infra\Adapter;

use src\Modules\Identity\Application\DTO\User\FindByIdUserInputDTO;
use src\Modules\Identity\Application\DTO\User\FindByIdUserOutputDTO;
use src\Modules\Identity\Application\Interfaces\Adapter\FindByIdUserDataAdapterInterface;

class FindByIdUserDataAdapter implements FindByIdUserDataAdapterInterface
{
    /**
     * @param  array{id?: string, name?: string, email?: string}  $data
     */
    public function fromArray(array $data): FindByIdUserInputDTO
    {
        return new FindByIdUserInputDTO(
            id: $data['id'] ?? null,
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
        );
    }

    /**
     * @return array{id: string, name: string, email: string, role: string, is_active: bool, created_at: string, must_change_password: bool}
     */
    public function toArray(FindByIdUserOutputDTO $data): array
    {
        return [
            'id' => $data->id,
            'name' => $data->name,
            'email' => $data->email,
            'role' => $data->role,
            'is_active' => $data->isActive,
            'created_at' => $data->createdAt->format(DATE_ATOM),
            'must_change_password' => $data->mustChangePassword,
        ];
    }
}
