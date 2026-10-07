<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\DTO;

readonly class FindReportsByUserIdAndCoordinateIdInputDTO
{
    public function __construct(
        public string $userId,
        public string $coordinateId,
    ) {}
}
