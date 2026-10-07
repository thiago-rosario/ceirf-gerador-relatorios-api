<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\DTO;

readonly class CountAllReportsOutputDTO
{
    public function __construct(
        public int $count,
    ) {}
}
