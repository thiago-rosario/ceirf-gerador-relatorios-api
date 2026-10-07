<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\DTO;

readonly class ReportLocationDataDTO
{
    public function __construct(
        public ?ReportImageDataDTO $locationMap,
        public ?ReportImageDataDTO $municipalityInStateMap,
    ) {}
}
