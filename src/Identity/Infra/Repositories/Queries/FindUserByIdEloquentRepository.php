<?php

declare(strict_types=1);

namespace src\Identity\Infra\Repositories\Queries;

use App\Models\User as UserModel;
use src\Identity\Domain\Entity\UserEntity;

class FindUserByIdEloquentRepository
{
    public function findById(string $id): ?UserEntity
    {
        $model = UserModel::query()->find($id);

        return $model === null ? null : UserEntity::fromModel($model);
    }
}
