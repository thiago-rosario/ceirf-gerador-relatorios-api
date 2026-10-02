<?php

declare(strict_types=1);

namespace src\Identity\Infra\Repositories\Queries;

use App\Models\User as UserModel;
use src\Identity\Domain\Entity\UserEntity;

class FindUserByEmailEloquentRepository
{
    public function findByEmail(string $email): ?UserEntity
    {
        $model = UserModel::query()->where('email', $email)->first();

        return $model === null ? null : UserEntity::fromModel($model);
    }
}
