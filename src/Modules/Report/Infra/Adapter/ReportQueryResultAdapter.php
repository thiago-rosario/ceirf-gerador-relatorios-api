<?php

declare(strict_types=1);

namespace src\Modules\Report\Infra\Adapter;

use src\Modules\Report\Application\DTO\MonthlyReportCountDataDTO;
use src\Modules\Report\Application\DTO\ReportDataDTO;
use src\Modules\Report\Application\Interfaces\Adapter\ReportQueryResultAdapterInterface;
use src\Modules\Report\Application\Interfaces\Service\ReportDataMapperServiceInterface;
use src\Modules\Report\Domain\Entity\ReportEntity;
use UnexpectedValueException;

class ReportQueryResultAdapter implements ReportQueryResultAdapterInterface
{
    public function __construct(
        private readonly ReportDataMapperServiceInterface $mapper,
    ) {}

    /**
     * @param  array<array-key, mixed>  $data
     * @return list<ReportDataDTO>
     */
    public function toReports(array $data): array
    {
        $reports = [];

        foreach ($data as $report) {
            if (! $report instanceof ReportEntity) {
                throw new UnexpectedValueException('A consulta de relatórios deve retornar entidades de relatório.');
            }

            $reports[] = $this->mapper->map($report);
        }

        return $reports;
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return list<MonthlyReportCountDataDTO>
     */
    public function toMonthlyCounts(array $data): array
    {
        $counts = [];

        foreach ($data as $row) {
            if (! is_array($row) || ! is_string($row['month'] ?? null) || ! is_int($row['count'] ?? null)) {
                throw new UnexpectedValueException('A contagem mensal deve conter um mês e uma quantidade inteira.');
            }

            $counts[] = new MonthlyReportCountDataDTO(
                month: $row['month'],
                count: $row['count'],
            );
        }

        return $counts;
    }
}
