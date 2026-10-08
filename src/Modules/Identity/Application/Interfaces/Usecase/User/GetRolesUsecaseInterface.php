<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\Interfaces\Usecase\User;

use src\Modules\Identity\Application\DTO\User\GetRolesOutputDTO;

interface GetRolesUsecaseInterface
{
    public function __invoke(): GetRolesOutputDTO;
}
