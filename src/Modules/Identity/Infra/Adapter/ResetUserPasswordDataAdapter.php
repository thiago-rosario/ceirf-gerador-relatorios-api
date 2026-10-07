<?php

declare(strict_types=1);

namespace src\Modules\Identity\Infra\Adapter;

use src\Modules\Identity\Application\DTO\Auth\ResetUserPasswordInputDTO;
use src\Modules\Identity\Application\DTO\Auth\ResetUserPasswordOutputDTO;
use src\Modules\Identity\Application\Interfaces\Adapter\ResetUserPasswordDataAdapterInterface;

class ResetUserPasswordDataAdapter implements ResetUserPasswordDataAdapterInterface
{
    /**
     * @param  array{id: string}  $data
     */
    public function fromArray(array $data): ResetUserPasswordInputDTO
    {
        return new ResetUserPasswordInputDTO(id: $data['id']);
    }

    /**
     * @return array{id: string, must_change_password: bool}
     */
    public function toArray(ResetUserPasswordOutputDTO $data): array
    {
        return [
            'id' => $data->id,
            'must_change_password' => $data->mustChangePassword,
        ];
    }
}
