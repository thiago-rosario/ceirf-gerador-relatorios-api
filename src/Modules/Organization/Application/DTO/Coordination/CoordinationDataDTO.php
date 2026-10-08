<?php

declare(strict_types=1);

namespace src\Modules\Organization\Application\DTO\Coordination;

readonly class CoordinationDataDTO
{
    public function __construct(
        public int $id,
        public string $code,
        public string $name,
    ) {}
}
