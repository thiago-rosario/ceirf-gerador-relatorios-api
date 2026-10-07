<?php

declare(strict_types=1);

namespace src\Modules\Identity\Infra\Repositories\Queries;

use src\Modules\Identity\Domain\Entity\UserEntity;
use src\Modules\Identity\Model\User as UserModel;

class FindUserByNameEloquentRepository
{
    public function findByName(string $name): ?UserEntity
    {
        $model = UserModel::query()->with('roles')->where('name', $name)->first();

        return $model === null ? null : UserEntity::fromModel($model);
    }
}
