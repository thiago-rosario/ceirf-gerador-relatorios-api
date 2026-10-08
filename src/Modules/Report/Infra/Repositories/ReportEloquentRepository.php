<?php

declare(strict_types=1);

namespace src\Modules\Report\Infra\Repositories;

use src\Modules\Report\Domain\Entity\ReportEntity;
use src\Modules\Report\Domain\Repository\ReportRepositoryInterface;
use src\Modules\Report\Infra\Repositories\Commands\CreateReportEloquentRepository;
use src\Modules\Report\Infra\Repositories\Commands\UpdateReportEloquentRepository;
use src\Modules\Report\Infra\Repositories\Queries\FindByIdReportEloquentRepository;
use src\Modules\Report\Infra\Repositories\Queries\FindLatestReportEloquentRepository;
use src\Modules\Report\Infra\Repositories\Queries\FindReportHistoryEloquentRepository;
use src\Modules\Report\Infra\Repositories\Queries\ReportCountsEloquentRepository;
use src\Modules\Report\Infra\Repositories\Queries\ReportListEloquentRepository;

final readonly class ReportEloquentRepository implements ReportRepositoryInterface
{
    public function __construct(
        private CreateReportEloquentRepository $createReportRepository,
        private UpdateReportEloquentRepository $updateReportRepository,
        private FindByIdReportEloquentRepository $findByIdReportRepository,
        private FindReportHistoryEloquentRepository $findReportHistoryRepository,
        private FindLatestReportEloquentRepository $findLatestReportRepository,
        private ReportListEloquentRepository $reportListRepository,
        private ReportCountsEloquentRepository $reportCountsRepository,
    ) {}

    public function insert(ReportEntity $report): ReportEntity
    {
        return $this->createReportRepository->insert($report);
    }

    public function update(ReportEntity $report): ReportEntity
    {
        return $this->updateReportRepository->update($report);
    }

    public function findById(string $id): ?ReportEntity
    {
        return $this->findByIdReportRepository->findById($id);
    }

    /** @return list<ReportEntity> */
    public function findByRootReportId(string $rootReportId): array
    {
        return $this->findReportHistoryRepository->findByRootReportId($rootReportId);
    }

    public function findLatestByRootReportId(string $rootReportId): ?ReportEntity
    {
        return $this->findLatestReportRepository->findLatestByRootReportId($rootReportId);
    }

    /** @return list<ReportEntity> */
    public function getReportByUserId(string $userId): ?array
    {
        return $this->reportListRepository->getReportByUserId($userId);
    }

    /** @return list<ReportEntity> */
    public function getReportByCoordinateId(string $coordinateId): ?array
    {
        return $this->reportListRepository->getReportByCoordinateId($coordinateId);
    }

    /** @return list<ReportEntity> */
    public function getReportByUserIdAndCoordinateId(string $userId, string $coordinateId): ?array
    {
        return $this->reportListRepository->getReportByUserIdAndCoordinateId($userId, $coordinateId);
    }

    public function countAllReports(): int
    {
        return $this->reportCountsRepository->countAllReports();
    }

    /** @return list<array{month: string, count: int}> */
    public function countReportsByMonth(string $month): ?array
    {
        return $this->reportCountsRepository->countReportsByMonth($month);
    }

    /** @return list<ReportEntity> */
    public function getDashboardReports(): ?array
    {
        return $this->reportListRepository->getDashboardReports();
    }

    /** @return list<ReportEntity>|null */
    public function findLatestReportById(string $id): ?array
    {
        return $this->reportListRepository->findLatestReportById($id);
    }

    /** @return list<ReportEntity> */
    public function findReportByMunicipalityId(string $municipalityId): ?array
    {
        return $this->reportListRepository->findReportByMunicipalityId($municipalityId);
    }
}
