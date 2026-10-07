<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\DTO;

readonly class CreateReportRevisionInputDTO
{
    public function __construct(
        public string $id,
        public ?string $createdBy = null,
    ) {}
}
