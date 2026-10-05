<?php

declare(strict_types=1);

namespace src\Identity\Infra\Repositories\Commands;

use App\Model\Role;
use App\Model\User as UserModel;
use Illuminate\Support\Facades\DB;
use src\Identity\Domain\Entity\UserEntity;

class CreateUserEloquentRepository
{
    public function insert(UserEntity $user): UserEntity
    {
        return DB::transaction(function () use ($user): UserEntity {
            $model = UserModel::query()->create([
                'uuid' => $user->id()->value(),
                'email' => $user->email()->value(),
                'name' => $user->name(),
                'password' => $user->password(),
                'is_active' => $user->isActive(),
                'must_change_password' => $user->mustChangePassword(),
                'created_at' => $user->createdAt(),
                'updated_at' => $user->updatedAt(),
            ]);

            $model->roles()->sync([Role::forRole($user->role())->id]);

            return UserEntity::fromModel($model->load('roles'));
        });
    }
}
