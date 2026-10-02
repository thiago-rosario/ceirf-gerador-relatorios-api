<?php

declare(strict_types=1);

namespace src\Identity\Infra\Repositories\Queries;

use App\Models\User as UserModel;
use src\Identity\Domain\Entity\UserEntity;

class FindUserByNameEloquentRepository
{
    public function findByName(string $name): ?UserEntity
    {
        $model = UserModel::query()->where('name', $name)->first();

        return $model === null ? null : UserEntity::fromModel($model);
    }
}
