<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\Service;

use src\Modules\Report\Application\Exception\ReportNotFoundException;
use src\Modules\Report\Application\Interfaces\Service\ReportFinderServiceInterface;
use src\Modules\Report\Domain\Entity\ReportEntity;
use src\Modules\Report\Domain\Repository\ReportRepositoryInterface;

class ReportFinderService implements ReportFinderServiceInterface
{
    public function __construct(
        private readonly ReportRepositoryInterface $repository,
    ) {}

    public function findById(string $id): ReportEntity
    {
        $report = $this->repository->findById($id);

        if ($report === null) {
            throw new ReportNotFoundException;
        }

        return $report;
    }
}
