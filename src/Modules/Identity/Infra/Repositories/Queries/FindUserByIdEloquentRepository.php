<?php

declare(strict_types=1);

namespace src\Modules\Identity\Infra\Repositories\Queries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use src\Modules\Identity\Domain\Entity\UserEntity;
use src\Modules\Identity\Model\User as UserModel;

class FindUserByIdEloquentRepository
{
    public function findById(string $id): ?UserEntity
    {
        $model = UserModel::query()
            ->with('roles')
            ->when(DB::transactionLevel() > 0, fn (Builder $query): Builder => $query->lockForUpdate())
            ->where('uuid', $id)
            ->first();

        return $model === null ? null : UserEntity::fromModel($model);
    }
}
