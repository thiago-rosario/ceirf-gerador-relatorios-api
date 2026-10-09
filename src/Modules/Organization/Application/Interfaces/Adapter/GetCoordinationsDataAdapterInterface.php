<?php

declare(strict_types=1);

namespace src\Modules\Organization\Application\Interfaces\Adapter;

use src\Modules\Organization\Application\DTO\GetCoordinationsOutputDTO;

interface GetCoordinationsDataAdapterInterface
{
    /**
     * @return array{coordinations: list<array{id: int, code: string, name: string}>}
     */
    public function toArray(GetCoordinationsOutputDTO $data): array;
}
