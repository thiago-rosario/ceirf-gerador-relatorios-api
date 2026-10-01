<?php

declare(strict_types=1);

namespace Identity\Application\Usecase\User;

use Identity\Application\Interfaces\Usecase\User\CreateUserUsecaseInterface;
use src\Identity\Application\DTO\User\CreateUserInputDTO;
use src\Identity\Application\DTO\User\CreateUserOutputDTO;

class CreateUserUsecase implements CreateUserUsecaseInterface
{
    public function __invoke(CreateUserInputDTO $input): CreateUserOutputDTO
    {
        
    }
}
