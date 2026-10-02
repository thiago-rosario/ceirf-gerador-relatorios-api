<?php

declare(strict_types=1);

namespace src\Identity\Infra\Repositories\Commands;

use App\Models\User as UserModel;
use src\Identity\Domain\Entity\UserEntity;

class CreateUserEloquentRepository
{
    public function insert(UserEntity $user): UserEntity
    {
        $model = UserModel::query()->create([
            'id' => $user->id()->value(),
            'email' => $user->email()->value(),
            'name' => $user->name(),
            'password' => $user->password(),
            'role' => $user->role()->value,
            'is_active' => $user->isActive(),
            'must_change_password' => $user->mustChangePassword(),
            'created_at' => $user->createdAt(),
            'updated_at' => $user->updatedAt(),
        ]);

        return UserEntity::fromModel($model);
    }
}
