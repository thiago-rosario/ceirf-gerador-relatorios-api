<?php

declare(strict_types=1);

namespace src\Identity\Infra\Repositories\Queries;

use App\Model\User as UserModel;
use src\Identity\Domain\Entity\UserEntity;

class FindUserByNameEloquentRepository
{
    public function findByName(string $name): ?UserEntity
    {
        $model = UserModel::query()->with('roles')->where('name', $name)->first();

        return $model === null ? null : UserEntity::fromModel($model);
    }
}
