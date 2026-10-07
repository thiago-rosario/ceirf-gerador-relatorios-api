<?php

declare(strict_types=1);

namespace src\Modules\Identity\Infra\Adapter;

use src\Modules\Identity\Application\DTO\Auth\LogoutUserInputDTO;
use src\Modules\Identity\Application\Interfaces\Adapter\LogoutUserDataAdapterInterface;

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
