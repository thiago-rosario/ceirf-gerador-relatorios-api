<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\Interfaces\Service;

use src\Modules\Report\Domain\Entity\ReportEntity;

interface ReportFinderServiceInterface
{
    public function findById(string $id): ReportEntity;
}
