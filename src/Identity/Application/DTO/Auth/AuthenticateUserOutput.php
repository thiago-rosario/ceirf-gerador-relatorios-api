<?php

declare(strict_types=1);

namespace Identity\Application\DTO\Auth;

use src\Identity\Application\DTO\User\UserDataDTO;

readonly class AuthenticateUserOutputDTO
{
    public function __construct(
        public string $accessToken,
        public UserDataDTO $user,
    ){}
}
