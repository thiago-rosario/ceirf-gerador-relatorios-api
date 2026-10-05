<?php

declare(strict_types=1);

namespace src\Identity\Infra\Repositories\Auth;

use App\Model\User as UserModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use src\Identity\Domain\Entity\UserEntity;

class CreateAccessTokenEloquentRepository
{
    public function createAccessToken(UserEntity $user): string
    {
        $model = UserModel::query()->where('uuid', $user->id()->value())->firstOrFail();
        $accessToken = Str::random(64);

        DB::table('user_access_tokens')->insert([
            'user_id' => $model->id,
            'token' => hash('sha256', $accessToken),
            'created_at' => now(),
        ]);

        return $accessToken;
    }
}
