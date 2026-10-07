<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\Interfaces\Usecase;

use src\Modules\Report\Application\DTO\CountAllReportsOutputDTO;

interface CountAllReportsUsecaseInterface
{
    public function __invoke(): CountAllReportsOutputDTO;
}
