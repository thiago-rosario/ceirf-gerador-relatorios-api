<?php

declare(strict_types=1);

namespace src\Identity\Application\Interfaces\Usecase\User;

use src\Identity\Application\DTO\User\UpdateUserInputDTO;
use src\Identity\Application\DTO\User\UpdateUserOutputDTO;

interface UpdateUserUsecaseInterface
{
    public function __invoke(UpdateUserInputDTO $input): UpdateUserOutputDTO;
}
