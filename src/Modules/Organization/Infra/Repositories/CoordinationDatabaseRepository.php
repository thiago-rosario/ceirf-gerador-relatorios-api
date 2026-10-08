<?php

declare(strict_types=1);

namespace src\Modules\Organization\Infra\Repositories;

use Illuminate\Support\Facades\DB;
use src\Modules\Organization\Domain\Entity\CoordinationEntity;
use src\Modules\Organization\Domain\Repository\CoordinationRepositoryInterface;
use stdClass;

class CoordinationDatabaseRepository implements CoordinationRepositoryInterface
{
    /**
     * @return list<CoordinationEntity>
     */
    public function findAll(): array
    {
        $coordinations = DB::table('coordinations')
            ->where('is_active', true)
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'code', 'name'])
            ->map(fn (stdClass $coordination): CoordinationEntity => new CoordinationEntity(
                id: (int) $coordination->id,
                code: (string) $coordination->code,
                name: (string) $coordination->name,
            ))
            ->all();

        return array_values($coordinations);
    }
}
