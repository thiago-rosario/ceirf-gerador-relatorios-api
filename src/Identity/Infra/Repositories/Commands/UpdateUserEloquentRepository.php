<?php

declare(strict_types=1);

namespace src\Identity\Infra\Repositories\Commands;

use App\Models\User as UserModel;
use src\Identity\Domain\Entity\UserEntity;

class UpdateUserEloquentRepository
{
    public function update(UserEntity $user): UserEntity
    {
        $model = UserModel::query()->findOrFail($user->id()->value());

        $model->update([
            'email' => $user->email()->value(),
            'name' => $user->name(),
            'password' => $user->password(),
            'role' => $user->role()->value,
            'is_active' => $user->isActive(),
            'must_change_password' => $user->mustChangePassword(),
            'updated_at' => $user->updatedAt(),
        ]);

        return UserEntity::fromModel($model);
    }
}
