<?php

declare(strict_types=1);

namespace src\Identity\Application\Interfaces\Auth;

use src\Identity\Application\DTO\Auth\AuthenticateUserInputDTO;
use src\Identity\Application\DTO\Auth\AuthenticateUserOutputDTO;

interface AuthenticateUserUsecaseInterface
{
    public function __invoke(AuthenticateUserInputDTO $input): AuthenticateUserOutputDTO;
}
