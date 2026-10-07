<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\Interfaces\Usecase\Auth;

use src\Modules\Identity\Application\DTO\Auth\ResetUserPasswordInputDTO;
use src\Modules\Identity\Application\DTO\Auth\ResetUserPasswordOutputDTO;

interface ResetUserPasswordUsecaseInterface
{
    public function __invoke(ResetUserPasswordInputDTO $input): ResetUserPasswordOutputDTO;
}
