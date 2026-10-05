<?php

declare(strict_types=1);

namespace src\Identity\Application\Interfaces\Adapter;

use src\Identity\Application\DTO\Auth\AuthenticateUserInputDTO;
use src\Identity\Application\DTO\Auth\AuthenticateUserOutputDTO;

interface AuthenticateUserAdapterInterface
{
    /**
     * @param  array{email: string, password: string}  $data
     */
    public function fromArray(array $data): AuthenticateUserInputDTO;

    /**
     * @return array{access_token: string, user: array{id: string, name: string, email: string, role: string, must_change_password: bool}}
     */
    public function toArray(AuthenticateUserOutputDTO $data): array;
}
