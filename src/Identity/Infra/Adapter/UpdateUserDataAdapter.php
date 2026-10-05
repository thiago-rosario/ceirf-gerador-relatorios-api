<?php

declare(strict_types=1);

namespace src\Identity\Infra\Adapter;

use src\Identity\Application\DTO\User\UpdateUserInputDTO;
use src\Identity\Application\DTO\User\UpdateUserOutputDTO;
use src\Identity\Application\Interfaces\Adapter\UpdateUserDataAdapterInterface;
use src\Identity\Domain\Enum\UserRoleEnum;

class UpdateUserDataAdapter implements UpdateUserDataAdapterInterface
{
    /**
     * @param  array{id: string, name?: string, email?: string, password?: string, role?: string}  $data
     */
    public function fromArray(array $data): UpdateUserInputDTO
    {
        return new UpdateUserInputDTO(
            id: $data['id'],
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
            password: $data['password'] ?? null,
            role: isset($data['role']) ? UserRoleEnum::from($data['role']) : null,
        );
    }

    /**
     * @return array{id: string, name: string, email: string, role: string, is_active: bool, created_at: string, updated_at: string}
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
        ];
    }
}
