<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\DTO\User;

readonly class ListAllUserInputDTO
{
    public function __construct(
        public string $filter = '',
        public string $orderBy = 'DESC',
    ) {}
}
