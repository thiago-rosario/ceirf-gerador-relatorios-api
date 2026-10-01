<?php

declare(strict_types=1);

namespace src\Identity\Application\Interfaces\Auth;

use src\Identity\Application\DTO\Auth\LogoutUserInputDTO;

interface LogoutUserUsecaseInterface
{
    public function __invoke(LogoutUserInputDTO $input): void;
}
