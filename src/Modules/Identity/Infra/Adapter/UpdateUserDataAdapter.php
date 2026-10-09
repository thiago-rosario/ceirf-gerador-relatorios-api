<?php

declare(strict_types=1);

namespace src\Modules\Identity\Infra\Adapter;

use src\Modules\Identity\Application\DTO\User\UpdateUserInputDTO;
use src\Modules\Identity\Application\DTO\User\UpdateUserOutputDTO;
use src\Modules\Identity\Application\Interfaces\Adapter\UpdateUserDataAdapterInterface;
use src\Modules\Identity\Domain\Enum\UserRoleEnum;

class UpdateUserDataAdapter implements UpdateUserDataAdapterInterface
{
    /**
     * @param  array{id: string, name?: string, email?: string, password?: string, role?: string, coordination_id?: int|null}  $data
     */
    public function fromArray(array $data): UpdateUserInputDTO
    {
        return new UpdateUserInputDTO(
            id: $data['id'],
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
            password: $data['password'] ?? null,
            role: isset($data['role']) ? UserRoleEnum::from($data['role']) : null,
            coordinationId: $data['coordination_id'] ?? null,
            coordinationIdProvided: array_key_exists('coordination_id', $data),
        );
    }

    /**
     * @return array{id: string, name: string, email: string, role: string, is_active: bool, created_at: string, updated_at: string, coordination_id: int|null, coordination: array{id: int, code: string, name: string}|null}
     */
    public function toArray(UpdateUserOutputDTO $data): array
    {
        return [
            'id' => $data->id,
            'name' => $data->name,
            'email' => $data->email,
            'role' => $data->role,
            'is_active' => $data->isActive,
            'created_at' => $data->createdAt->format(DATE_ATOM),
            'updated_at' => $data->updatedAt->format(DATE_ATOM),
            'coordination_id' => $data->coordinationId,
            'coordination' => $data->coordination === null ? null : [
                'id' => $data->coordination->id,
                'code' => $data->coordination->code,
                'name' => $data->coordination->name,
            ],
        ];
    }
}
