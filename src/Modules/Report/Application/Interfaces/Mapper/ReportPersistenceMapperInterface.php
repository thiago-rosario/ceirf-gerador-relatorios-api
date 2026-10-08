<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\Interfaces\Mapper;

use src\Modules\Report\Domain\Entity\ReportEntity;

interface ReportPersistenceMapperInterface
{
    /** @return array<string, mixed> */
    public function toPayload(ReportEntity $report): array;

    /** @param array<string, mixed> $payload */
    public function fromPayload(array $payload): ReportEntity;

    /** @return array<string, mixed> */
    public function toAttributes(ReportEntity $report): array;
}
