<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\Interfaces\Usecase\User;

use src\Modules\Identity\Application\DTO\User\CreateUserInputDTO;
use src\Modules\Identity\Application\DTO\User\CreateUserOutputDTO;

interface CreateUserUsecaseInterface
{
    public function __invoke(CreateUserInputDTO $input): CreateUserOutputDTO;
}
