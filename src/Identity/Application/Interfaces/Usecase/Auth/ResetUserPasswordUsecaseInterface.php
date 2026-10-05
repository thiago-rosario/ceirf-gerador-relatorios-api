<?php

declare(strict_types=1);

namespace src\Identity\Application\Interfaces\Usecase\Auth;

use src\Identity\Application\DTO\Auth\ResetUserPasswordInputDTO;
use src\Identity\Application\DTO\Auth\ResetUserPasswordOutputDTO;

interface ResetUserPasswordUsecaseInterface
{
    public function __invoke(ResetUserPasswordInputDTO $input): ResetUserPasswordOutputDTO;
}
