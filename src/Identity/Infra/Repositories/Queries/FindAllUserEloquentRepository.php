<?php

declare(strict_types=1);

namespace src\Identity\Infra\Repositories\Queries;

use App\Models\User as UserModel;
use Illuminate\Database\Eloquent\Builder;
use src\Identity\Domain\Entity\UserEntity;

class FindAllUserEloquentRepository
{
    /**
     * @return array<UserEntity>
     */
    public function findAll(string $filter = '', string $orderBy = 'DESC'): array
    {
        return UserModel::query()
            ->when($filter !== '', fn (Builder $query): Builder => $query->where(
                fn (Builder $filterQuery): Builder => $filterQuery
                    ->where('name', 'like', "%{$filter}%")
                    ->orWhere('email', 'like', "%{$filter}%"),
            ))
            ->orderBy('created_at', $orderBy)
            ->orderBy('id', $orderBy)
            ->get()
            ->map(fn (UserModel $model): UserEntity => UserEntity::fromModel($model))
            ->all();
    }
}
