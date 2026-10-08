<?php

declare(strict_types=1);

namespace src\Modules\Organization\Application\Interfaces\Usecase\Coordination;

use src\Modules\Organization\Application\DTO\Coordination\GetCoordinationsOutputDTO;

interface GetCoordinationsUsecaseInterface
{
    public function __invoke(): GetCoordinationsOutputDTO;
}
