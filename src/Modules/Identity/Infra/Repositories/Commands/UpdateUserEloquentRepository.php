<?php

declare(strict_types=1);

namespace src\Modules\Identity\Infra\Repositories\Commands;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\DB;
use src\Modules\Identity\Application\Exception\PasswordChangeRejectedException;
use src\Modules\Identity\Domain\Entity\UserEntity;
use src\Modules\Identity\Model\Role;
use src\Modules\Identity\Model\User as UserModel;

class UpdateUserEloquentRepository
{
    public function changePassword(UserEntity $user, string $previousPasswordHash, string $accessToken): UserEntity
    {
        return DB::transaction(function () use ($user, $previousPasswordHash, $accessToken): UserEntity {
            $model = UserModel::query()->with('roles')->where('uuid', $user->id()->value())->lockForUpdate()->first();

            if ($model === null || ! $model->is_active) {
                throw new AuthenticationException;
            }

            $tokenHash = hash('sha256', $accessToken);
            $currentToken = DB::table('user_access_tokens')
                ->where('user_id', $model->id)
                ->where('token', $tokenHash)
                ->lockForUpdate()
                ->first();

            if ($currentToken === null) {
                throw new AuthenticationException;
            }

            if ($model->password !== $previousPasswordHash) {
                throw new PasswordChangeRejectedException('current_password', 'A senha atual está incorreta.');
            }

            $model->fill([
                'password' => $user->password(),
                'must_change_password' => $user->mustChangePassword(),
                'updated_at' => $user->updatedAt(),
            ])->save();

            DB::table('user_access_tokens')->where('user_id', $model->id)->where('token', '!=', $tokenHash)->delete();

            return UserEntity::fromModel($model);
        });
    }

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
