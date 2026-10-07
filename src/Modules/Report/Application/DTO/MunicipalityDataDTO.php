<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\DTO;

readonly class MunicipalityDataDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $stateCode,
    ) {}
}
