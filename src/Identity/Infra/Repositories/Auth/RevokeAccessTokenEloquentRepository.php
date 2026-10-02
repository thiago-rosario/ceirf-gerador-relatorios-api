<?php

declare(strict_types=1);

namespace src\Identity\Infra\Repositories\Auth;

use Laravel\Sanctum\Sanctum;

class RevokeAccessTokenEloquentRepository
{
    public function revokeAccessToken(string $accessToken): void
    {
        $personalAccessTokenModel = Sanctum::personalAccessTokenModel();

        $personalAccessTokenModel::findToken($accessToken)?->delete();
    }
}
