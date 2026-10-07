<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\Interfaces\Usecase\User;

use src\Modules\Identity\Application\DTO\User\ListAllUserInputDTO;
use src\Modules\Identity\Application\DTO\User\ListAllUserOutputDTO;

interface ListAllUserUsecaseInterface
{
    public function __invoke(ListAllUserInputDTO $input): ListAllUserOutputDTO;
}
