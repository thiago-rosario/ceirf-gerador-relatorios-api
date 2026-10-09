<?php

declare(strict_types=1);

namespace src\Modules\Organization\Infra\Adapter;

use src\Modules\Organization\Application\DTO\CoordinationDataDTO;
use src\Modules\Organization\Application\DTO\GetCoordinationsOutputDTO;
use src\Modules\Organization\Application\Interfaces\Adapter\GetCoordinationsDataAdapterInterface;

class GetCoordinationsDataAdapter implements GetCoordinationsDataAdapterInterface
{
    /**
     * @return array{coordinations: list<array{id: int, code: string, name: string}>}
     */
    public function toArray(GetCoordinationsOutputDTO $data): array
    {
        return [
            'coordinations' => array_map(
                fn (CoordinationDataDTO $coordination): array => [
                    'id' => $coordination->id,
                    'code' => $coordination->code,
                    'name' => $coordination->name,
                ],
                $data->coordinations,
            ),
        ];
    }
}
