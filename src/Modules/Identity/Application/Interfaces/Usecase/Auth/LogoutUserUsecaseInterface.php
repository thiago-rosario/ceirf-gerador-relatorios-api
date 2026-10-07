<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\Interfaces\Usecase\Auth;

use src\Modules\Identity\Application\DTO\Auth\LogoutUserInputDTO;

interface LogoutUserUsecaseInterface
{
    public function __invoke(LogoutUserInputDTO $input): void;
}
