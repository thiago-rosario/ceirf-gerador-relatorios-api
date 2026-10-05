<?php

declare(strict_types=1);

namespace src\Identity\Infra\Adapter;

use src\Identity\Application\DTO\User\CreateUserInputDTO;
use src\Identity\Application\DTO\User\CreateUserOutputDTO;
use src\Identity\Application\Interfaces\Adapter\CreateUserDataAdapterInterface;
use src\Identity\Domain\Enum\UserRoleEnum;

class CreateUserDataAdapter implements CreateUserDataAdapterInterface
{
    /**
     * @param  array{name: string, email: string, password: string, role?: string}  $data
     */
    public function fromArray(array $data): CreateUserInputDTO
    {
        return new CreateUserInputDTO(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
            role: UserRoleEnum::from($data['role'] ?? UserRoleEnum::OPERATOR->value),
        );
    }

    /**
     * @return array{id: string, name: string, email: string, role: string, is_active: bool, created_at: string}
     */
    public function toArray(CreateUserOutputDTO $data): array
    {
        return [
            'id' => $data->id,
            'name' => $data->name,
            'email' => $data->email,
            'role' => $data->role,
            'is_active' => $data->isActive,
            'created_at' => $data->createdAt->format(DATE_ATOM),
        ];
    }
}
