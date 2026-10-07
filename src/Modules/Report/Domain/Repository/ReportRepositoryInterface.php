<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Repository;

use src\Modules\Report\Domain\Entity\ReportEntity;

interface ReportRepositoryInterface
{
    /**
     * A persistência deve garantir unicidade de família/revisão e preservar o histórico de uploads.
     */
    public function insert(ReportEntity $report): ReportEntity;

    public function findById(string $id): ?ReportEntity;

    /** @return list<ReportEntity> */
    public function findByRootReportId(string $rootReportId): array;

    /** @return array<array-key, mixed>|null */
    public function getReportByUserId(string $userId): ?array;

    /** @return array<array-key, mixed>|null */
    public function getReportByCoordinateId(string $coordinateId): ?array;

    /** @return array<array-key, mixed>|null */
    public function getReportByUserIdAndCoordinateId(string $userId, string $coordinateId): ?array;

    public function countAllReports(): int;

    /** @return array<array-key, mixed>|null */
    public function countReportsByMonth(string $month): ?array;

    /** @return array<array-key, mixed>|null */
    public function getDashboardReports(): ?array;

    /** @return array<array-key, mixed>|null */
    public function findLatestReportById(string $id): ?array;

    public function findLatestByRootReportId(string $rootReportId): ?ReportEntity;

    public function findReportByMunicipalityId(string $municipalityId): ?array;

    /**
     * A implementação deve impedir a sobrescrita de uma versão já gerada, inclusive entre processos.
     */
    public function update(ReportEntity $report): ReportEntity;
}
