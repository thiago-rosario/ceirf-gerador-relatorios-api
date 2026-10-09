<?php

declare(strict_types=1);

namespace src\Modules\Organization\Infra\Repositories;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use src\Modules\Organization\Domain\Entity\CoordinationEntity;
use src\Modules\Organization\Domain\Repository\CoordinationRepositoryInterface;
use stdClass;

class CoordinationDatabaseRepository implements CoordinationRepositoryInterface
{
    /**
     * @return list<CoordinationEntity>
     */
    public function findAll(bool $activeOnly = true): array
    {
        $coordinations = DB::table('coordinations')
            ->when($activeOnly, fn (Builder $query): Builder => $query->where('is_active', true))
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'code', 'name', 'is_active'])
            ->map($this->toEntity(...))
            ->all();

        return array_values($coordinations);
    }

    public function findById(int $id): ?CoordinationEntity
    {
        $coordination = DB::table('coordinations')->where('id', $id)->first(['id', 'code', 'name', 'is_active']);

        return $coordination === null ? null : $this->toEntity($coordination);
    }

    private function toEntity(stdClass $coordination): CoordinationEntity
    {
        return new CoordinationEntity(
            id: (int) $coordination->id,
            code: (string) $coordination->code,
            name: (string) $coordination->name,
            isActive: (bool) $coordination->is_active,
        );
    }
}
