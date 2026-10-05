<?php

declare(strict_types=1);

namespace src\Identity\Application\Interfaces\Usecase\User;

use src\Identity\Application\DTO\User\ListAllUserInputDTO;
use src\Identity\Application\DTO\User\ListAllUserOutputDTO;

interface ListAllUserUsecaseInterface
{
    public function __invoke(ListAllUserInputDTO $input): ListAllUserOutputDTO;
}
