<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\Interfaces\Usecase\User;

use src\Modules\Identity\Application\DTO\User\UpdateUserInputDTO;
use src\Modules\Identity\Application\DTO\User\UpdateUserOutputDTO;

interface UpdateUserUsecaseInterface
{
    public function __invoke(UpdateUserInputDTO $input): UpdateUserOutputDTO;
}
