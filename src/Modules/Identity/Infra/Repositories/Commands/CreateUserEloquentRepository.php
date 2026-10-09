<?php

declare(strict_types=1);

namespace src\Modules\Identity\Infra\Repositories\Commands;

use Illuminate\Support\Facades\DB;
use src\Modules\Identity\Domain\Entity\UserEntity;
use src\Modules\Identity\Model\Role;
use src\Modules\Identity\Model\User as UserModel;

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
                'coordination_id' => $user->coordinationId(),
                'created_at' => $user->createdAt(),
                'updated_at' => $user->updatedAt(),
            ]);

            $model->roles()->sync([Role::forRole($user->role())->id]);

            return UserEntity::fromModel($model->load('roles'));
        });
    }
}
