<?php

declare(strict_types=1);

namespace src\Modules\Identity\Infra\Adapter;

use src\Modules\Identity\Application\DTO\User\CreateUserInputDTO;
use src\Modules\Identity\Application\DTO\User\CreateUserOutputDTO;
use src\Modules\Identity\Application\Interfaces\Adapter\CreateUserDataAdapterInterface;
use src\Modules\Identity\Domain\Enum\UserRoleEnum;

class CreateUserDataAdapter implements CreateUserDataAdapterInterface
{
    /**
     * @param  array{name: string, email: string, password: string, role?: string, coordination_id?: int|null}  $data
     */
    public function fromArray(array $data): CreateUserInputDTO
    {
        return new CreateUserInputDTO(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
            role: UserRoleEnum::from($data['role'] ?? UserRoleEnum::OPERATOR->value),
            coordinationId: $data['coordination_id'] ?? null,
        );
    }

    /**
     * @return array{id: string, name: string, email: string, role: string, is_active: bool, created_at: string, coordination_id: int|null, coordination: array{id: int, code: string, name: string}|null}
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
            'coordination_id' => $data->coordinationId,
            'coordination' => $data->coordination === null ? null : [
                'id' => $data->coordination->id,
                'code' => $data->coordination->code,
                'name' => $data->coordination->name,
            ],
        ];
    }
}
