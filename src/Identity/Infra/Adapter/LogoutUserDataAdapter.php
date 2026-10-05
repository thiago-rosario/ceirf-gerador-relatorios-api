<?php

declare(strict_types=1);

namespace src\Identity\Infra\Adapter;

use src\Identity\Application\DTO\Auth\LogoutUserInputDTO;
use src\Identity\Application\Interfaces\Adapter\LogoutUserDataAdapterInterface;

class LogoutUserDataAdapter implements LogoutUserDataAdapterInterface
{
    /**
     * @param  array{access_token: string}  $data
     */
    public function fromArray(array $data): LogoutUserInputDTO
    {
        return new LogoutUserInputDTO(accessToken: $data['access_token']);
    }
}
