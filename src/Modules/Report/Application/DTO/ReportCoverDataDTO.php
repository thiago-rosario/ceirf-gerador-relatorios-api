<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\DTO;

readonly class ReportCoverDataDTO
{
    public function __construct(
        public ?MunicipalityDataDTO $municipality,
        public ?string $force,
        public ?string $size,
        public string $typology,
        public ?string $seiNumber,
    ) {}
}
