<?php

declare(strict_types=1);

namespace src\Identity\Infra\Repositories\Queries;

use App\Model\User as UserModel;
use src\Identity\Domain\Entity\UserEntity;

class FindUserByEmailEloquentRepository
{
    public function findByEmail(string $email): ?UserEntity
    {
        $model = UserModel::query()->with('roles')->where('email', $email)->first();

        return $model === null ? null : UserEntity::fromModel($model);
    }
}
