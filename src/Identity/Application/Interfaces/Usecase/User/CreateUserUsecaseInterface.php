<?php

declare(strict_types=1);

namespace src\Identity\Application\Interfaces\Usecase\User;

use src\Identity\Application\DTO\User\CreateUserInputDTO;
use src\Identity\Application\DTO\User\CreateUserOutputDTO;

interface CreateUserUsecaseInterface
{
    public function __invoke(CreateUserInputDTO $input): CreateUserOutputDTO;
}
