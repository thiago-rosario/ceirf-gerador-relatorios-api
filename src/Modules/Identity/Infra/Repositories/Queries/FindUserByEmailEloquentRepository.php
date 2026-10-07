<?php

declare(strict_types=1);

namespace src\Modules\Identity\Infra\Repositories\Queries;

use src\Modules\Identity\Domain\Entity\UserEntity;
use src\Modules\Identity\Model\User as UserModel;

class FindUserByEmailEloquentRepository
{
    public function findByEmail(string $email): ?UserEntity
    {
        $model = UserModel::query()->with('roles')->where('email', $email)->first();

        return $model === null ? null : UserEntity::fromModel($model);
    }
}
