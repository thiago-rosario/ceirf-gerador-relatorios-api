<?php

declare(strict_types=1);

namespace src\Identity\Application\Interfaces\Usecase\User;

use src\Identity\Application\DTO\User\FindByIdUserInputDTO;
use src\Identity\Application\DTO\User\FindByIdUserOutputDTO;

interface FindByIdUserUsecaseInterface
{
    public function __invoke(FindByIdUserInputDTO $input): FindByIdUserOutputDTO;
}
