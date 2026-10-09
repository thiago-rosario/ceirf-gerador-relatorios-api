<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\Interfaces\Usecase\Auth;

use src\Modules\Identity\Application\DTO\Auth\ChangeUserPasswordInputDTO;
use src\Modules\Identity\Application\DTO\User\FindByIdUserOutputDTO;

interface ChangeUserPasswordUsecaseInterface
{
    public function __invoke(ChangeUserPasswordInputDTO $input): FindByIdUserOutputDTO;
}
