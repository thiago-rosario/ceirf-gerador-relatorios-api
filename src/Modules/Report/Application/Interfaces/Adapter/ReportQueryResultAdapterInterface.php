<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\Interfaces\Adapter;

use src\Modules\Report\Application\DTO\MonthlyReportCountDataDTO;
use src\Modules\Report\Application\DTO\ReportDataDTO;

/**
 * Normaliza as projeções sem formato definido pelo contrato do repositório.
 * A interpretação das linhas pertence ao Adapter da infraestrutura.
 */
interface ReportQueryResultAdapterInterface
{
    /**
     * @param  array<array-key, mixed>  $data
     * @return list<ReportDataDTO>
     */
    public function toReports(array $data): array;

    /**
     * @param  array<array-key, mixed>  $data
     * @return list<MonthlyReportCountDataDTO>
     */
    public function toMonthlyCounts(array $data): array;
}
