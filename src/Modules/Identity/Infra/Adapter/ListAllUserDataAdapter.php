<?php

declare(strict_types=1);

namespace src\Modules\Identity\Infra\Adapter;

use src\Modules\Identity\Application\DTO\User\FindByIdUserOutputDTO;
use src\Modules\Identity\Application\DTO\User\ListAllUserInputDTO;
use src\Modules\Identity\Application\DTO\User\ListAllUserOutputDTO;
use src\Modules\Identity\Application\Interfaces\Adapter\FindByIdUserDataAdapterInterface;
use src\Modules\Identity\Application\Interfaces\Adapter\ListAllUserDataAdapterInterface;

class ListAllUserDataAdapter implements ListAllUserDataAdapterInterface
{
    public function __construct(
        private readonly FindByIdUserDataAdapterInterface $userAdapter,
    ) {}

    /**
     * @param  array{filter?: string, order_by?: string}  $data
     */
    public function fromArray(array $data): ListAllUserInputDTO
    {
        return new ListAllUserInputDTO(
            filter: $data['filter'] ?? '',
            orderBy: strtoupper($data['order_by'] ?? 'DESC'),
        );
    }

    /**
     * @return array{users: list<array{id: string, name: string, email: string, role: string, is_active: bool, created_at: string, must_change_password: bool}>}
     */
    public function toArray(ListAllUserOutputDTO $data): array
    {
        return [
            'users' => array_map(
                fn (FindByIdUserOutputDTO $user): array => $this->userAdapter->toArray($user),
                $data->users,
            ),
        ];
    }
}
