<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\DTO;

readonly class ReportConclusionDataDTO
{
    public function __construct(public string $content) {}
}
