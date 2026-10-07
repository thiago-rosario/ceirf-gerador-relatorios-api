<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\Interfaces\Usecase\User;

use src\Modules\Identity\Application\DTO\User\DeactivateUserInputDTO;
use src\Modules\Identity\Application\DTO\User\DeactivateUserOutputDTO;

interface DeactivateUserUsecaseInterface
{
    public function __invoke(DeactivateUserInputDTO $input): DeactivateUserOutputDTO;
}
