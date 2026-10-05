<?php

declare(strict_types=1);

namespace src\Identity\Application\Interfaces\Adapter;

use src\Identity\Application\DTO\Auth\LogoutUserInputDTO;

interface LogoutUserDataAdapterInterface
{
    /**
     * @param  array{access_token: string}  $data
     */
    public function fromArray(array $data): LogoutUserInputDTO;
}
