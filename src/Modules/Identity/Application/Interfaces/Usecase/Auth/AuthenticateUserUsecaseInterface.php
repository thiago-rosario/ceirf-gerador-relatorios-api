<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\Interfaces\Usecase\Auth;

use src\Modules\Identity\Application\DTO\Auth\AuthenticateUserInputDTO;
use src\Modules\Identity\Application\DTO\Auth\AuthenticateUserOutputDTO;

interface AuthenticateUserUsecaseInterface
{
    public function __invoke(AuthenticateUserInputDTO $input): AuthenticateUserOutputDTO;
}
