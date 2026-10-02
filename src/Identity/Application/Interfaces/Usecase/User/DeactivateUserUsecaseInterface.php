<?php

declare(strict_types=1);

namespace src\Identity\Application\Interfaces\Usecase\User;

use src\Identity\Application\DTO\User\DeactivateUserInputDTO;
use src\Identity\Application\DTO\User\DeactivateUserOutputDTO;

interface DeactivateUserUsecaseInterface
{
    public function __invoke(DeactivateUserInputDTO $input): DeactivateUserOutputDTO;
}
