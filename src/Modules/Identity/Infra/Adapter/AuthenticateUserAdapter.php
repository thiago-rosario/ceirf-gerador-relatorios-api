<?php

declare(strict_types=1);

namespace src\Modules\Identity\Infra\Adapter;

use src\Modules\Identity\Application\DTO\Auth\AuthenticateUserInputDTO;
use src\Modules\Identity\Application\DTO\Auth\AuthenticateUserOutputDTO;
use src\Modules\Identity\Application\Interfaces\Adapter\AuthenticateUserAdapterInterface;

class AuthenticateUserAdapter implements AuthenticateUserAdapterInterface
{
    /**
     * @param  array{email: string, password: string}  $data
     */
    public function fromArray(array $data): AuthenticateUserInputDTO
    {
        return new AuthenticateUserInputDTO(
            email: $data['email'],
            password: $data['password'],
        );
    }

    /**
     * @return array{access_token: string, user: array{id: string, name: string, email: string, role: string, must_change_password: bool}}
     */
    public function toArray(AuthenticateUserOutputDTO $data): array
    {
        return [
            'access_token' => $data->accessToken,
            'user' => [
                'id' => $data->user->id,
                'name' => $data->user->name,
                'email' => $data->user->email,
                'role' => $data->user->role,
                'must_change_password' => $data->user->mustChangePassword,
            ],
        ];
    }
}
