<?php

declare(strict_types=1);

namespace src\Identity\Infra\Repositories\Queries;

use App\Model\User as UserModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use src\Identity\Domain\Entity\UserEntity;

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
