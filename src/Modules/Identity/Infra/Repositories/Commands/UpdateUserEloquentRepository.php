<?php

declare(strict_types=1);

namespace src\Modules\Identity\Infra\Repositories\Commands;

use Illuminate\Support\Facades\DB;
use src\Modules\Identity\Domain\Entity\UserEntity;
use src\Modules\Identity\Model\Role;
use src\Modules\Identity\Model\User as UserModel;

class UpdateUserEloquentRepository
{
    public function update(UserEntity $user): UserEntity
    {
        return DB::transaction(function () use ($user): UserEntity {
            $model = UserModel::query()->with('roles')->where('uuid', $user->id()->value())->firstOrFail();
            $previousRole = $model->role;

            $model->fill([
                'email' => $user->email()->value(),
                'name' => $user->name(),
                'password' => $user->password(),
                'is_active' => $user->isActive(),
                'must_change_password' => $user->mustChangePassword(),
                'coordination_id' => $user->coordinationId(),
                'updated_at' => $user->updatedAt(),
            ]);

            $passwordWasChanged = $model->isDirty('password');
            $model->save();
            if ($previousRole !== $user->role()) {
                $model->roles()->sync([Role::forRole($user->role())->id]);
            }

            if ($passwordWasChanged || ! $user->isActive()) {
                DB::table('user_access_tokens')->where('user_id', $model->id)->delete();
            }

            return UserEntity::fromModel($model->load('roles'));
        });
    }
}
