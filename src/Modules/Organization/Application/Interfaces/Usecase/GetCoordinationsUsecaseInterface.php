<?php

declare(strict_types=1);

namespace src\Modules\Organization\Application\Interfaces\Usecase;

use src\Modules\Organization\Application\DTO\GetCoordinationsOutputDTO;

interface GetCoordinationsUsecaseInterface
{
    public function __invoke(): GetCoordinationsOutputDTO;
}
