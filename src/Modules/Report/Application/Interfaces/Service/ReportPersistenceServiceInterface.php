<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\Interfaces\Service;

use src\Modules\Report\Domain\Entity\ReportEntity;
use src\Modules\Report\Domain\Entity\ReportImageEntity;

interface ReportPersistenceServiceInterface
{
    public function authorId(ReportEntity $report): int;

    /** @return array<string, mixed> */
    public function attributes(ReportEntity $report): array;

    /** @param list<ReportImageEntity> $previousImages */
    public function preserveUploadedImages(ReportEntity $report, array $previousImages): ReportEntity;

    public function storeMedia(ReportEntity $report, int $reportId, int $authorId, ?int $parentReportId = null): void;
}
