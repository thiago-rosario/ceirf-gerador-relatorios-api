<?php

declare(strict_types=1);

namespace src\Modules\Identity\Infra\Adapter;

use src\Modules\Identity\Application\DTO\User\DeactivateUserInputDTO;
use src\Modules\Identity\Application\DTO\User\DeactivateUserOutputDTO;
use src\Modules\Identity\Application\Interfaces\Adapter\DeactivateUserDataAdapterInterface;

class DeactivateUserDataAdapter implements DeactivateUserDataAdapterInterface
{
    /**
     * @param  array{id: string}  $data
     */
    public function fromArray(array $data): DeactivateUserInputDTO
    {
        return new DeactivateUserInputDTO(id: $data['id']);
    }

    /**
     * @return array{id: string, name: string, email: string, is_active: bool}
     */
    public function toArray(DeactivateUserOutputDTO $data): array
    {
        return [
            'id' => $data->id,
            'name' => $data->name,
            'email' => $data->email,
            'is_active' => $data->active,
        ];
    }
}
