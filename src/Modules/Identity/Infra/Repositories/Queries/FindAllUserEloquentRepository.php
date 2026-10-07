<?php

declare(strict_types=1);

namespace src\Modules\Identity\Infra\Repositories\Queries;

use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;
use src\Modules\Identity\Domain\Entity\UserEntity;
use src\Modules\Identity\Model\User as UserModel;

class FindAllUserEloquentRepository
{
    /**
     * @return array<UserEntity>
     */
    public function findAll(string $filter = '', string $orderBy = 'DESC'): array
    {
        $direction = match (strtoupper($orderBy)) {
            'ASC' => 'asc',
            'DESC' => 'desc',
            default => throw new InvalidArgumentException('A ordenação deve ser ASC ou DESC.'),
        };

        return UserModel::query()
            ->with('roles')
            ->when($filter !== '', fn (Builder $query): Builder => $query->where(
                fn (Builder $filterQuery): Builder => $filterQuery
                    ->where('name', 'like', "%{$filter}%")
                    ->orWhere('email', 'like', "%{$filter}%"),
            ))
            ->orderBy('created_at', $direction)
            ->orderBy('id', $direction)
            ->get()
            ->map(fn (UserModel $model): UserEntity => UserEntity::fromModel($model))
            ->all();
    }
}
