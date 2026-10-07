<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\Interfaces\Usecase;

use src\Modules\Report\Application\DTO\GetDashboardReportsOutputDTO;

interface GetDashboardReportsUsecaseInterface
{
    public function __invoke(): GetDashboardReportsOutputDTO;
}
