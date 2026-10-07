<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\Interfaces\Usecase\User;

use src\Modules\Identity\Application\DTO\User\FindByIdUserInputDTO;
use src\Modules\Identity\Application\DTO\User\FindByIdUserOutputDTO;

interface FindByIdUserUsecaseInterface
{
    public function __invoke(FindByIdUserInputDTO $input): FindByIdUserOutputDTO;
}
