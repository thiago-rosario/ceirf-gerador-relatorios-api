<?php

declare(strict_types=1);

namespace src\Modules\Identity\Infra\Provider;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use src\Modules\Identity\Application\Interfaces\Adapter\AuthenticateUserAdapterInterface;
use src\Modules\Identity\Application\Interfaces\Adapter\CreateUserDataAdapterInterface;
use src\Modules\Identity\Application\Interfaces\Adapter\DeactivateUserDataAdapterInterface;
use src\Modules\Identity\Application\Interfaces\Adapter\FindByIdUserDataAdapterInterface;
use src\Modules\Identity\Application\Interfaces\Adapter\ListAllUserDataAdapterInterface;
use src\Modules\Identity\Application\Interfaces\Adapter\LogoutUserDataAdapterInterface;
use src\Modules\Identity\Application\Interfaces\Adapter\ResetUserPasswordDataAdapterInterface;
use src\Modules\Identity\Application\Interfaces\Adapter\UpdateUserDataAdapterInterface;
use src\Modules\Identity\Application\Interfaces\Service\PasswordHasherServiceInterface;
use src\Modules\Identity\Application\Interfaces\Service\UserAuthenticatorServiceInterface;
use src\Modules\Identity\Application\Interfaces\Usecase\Auth\AuthenticateUserUsecaseInterface;
use src\Modules\Identity\Application\Interfaces\Usecase\Auth\LogoutUserUsecaseInterface;
use src\Modules\Identity\Application\Interfaces\Usecase\Auth\ResetUserPasswordUsecaseInterface;
use src\Modules\Identity\Application\Interfaces\Usecase\User\CreateUserUsecaseInterface;
use src\Modules\Identity\Application\Interfaces\Usecase\User\DeactivateUserUsecaseInterface;
use src\Modules\Identity\Application\Interfaces\Usecase\User\FindByIdUserUsecaseInterface;
use src\Modules\Identity\Application\Interfaces\Usecase\User\ListAllUserUsecaseInterface;
use src\Modules\Identity\Application\Interfaces\Usecase\User\UpdateUserUsecaseInterface;
use src\Modules\Identity\Application\Service\UserAuthenticatorService;
use src\Modules\Identity\Application\Usecase\Auth\AuthenticateUserUsecase;
use src\Modules\Identity\Application\Usecase\Auth\LogoutUserUsecase;
use src\Modules\Identity\Application\Usecase\Auth\ResetUserPasswordUsecase;
use src\Modules\Identity\Application\Usecase\User\CreateUserUsecase;
use src\Modules\Identity\Application\Usecase\User\DeactivateUserUsecase;
use src\Modules\Identity\Application\Usecase\User\FindByIdUserUsecase;
use src\Modules\Identity\Application\Usecase\User\ListAllUserUsecase;
use src\Modules\Identity\Application\Usecase\User\UpdateUserUsecase;
use src\Modules\Identity\Domain\Enum\UserRoleEnum;
use src\Modules\Identity\Domain\Repository\UserRepositoryInterface;
use src\Modules\Identity\Infra\Adapter\AuthenticateUserAdapter;
use src\Modules\Identity\Infra\Adapter\CreateUserDataAdapter;
use src\Modules\Identity\Infra\Adapter\DeactivateUserDataAdapter;
use src\Modules\Identity\Infra\Adapter\FindByIdUserDataAdapter;
use src\Modules\Identity\Infra\Adapter\ListAllUserDataAdapter;
use src\Modules\Identity\Infra\Adapter\LogoutUserDataAdapter;
use src\Modules\Identity\Infra\Adapter\ResetUserPasswordDataAdapter;
use src\Modules\Identity\Infra\Adapter\UpdateUserDataAdapter;
use src\Modules\Identity\Infra\Repositories\UserEloquentRepository;
use src\Modules\Identity\Infra\Service\PasswordHasherService;
use src\Modules\Identity\Model\User;

class IdentityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserEloquentRepository::class);

        $this->app->bind(PasswordHasherServiceInterface::class, PasswordHasherService::class);
        $this->app->bind(UserAuthenticatorServiceInterface::class, UserAuthenticatorService::class);

        $this->app->bind(AuthenticateUserAdapterInterface::class, AuthenticateUserAdapter::class);
        $this->app->bind(LogoutUserDataAdapterInterface::class, LogoutUserDataAdapter::class);
        $this->app->bind(ResetUserPasswordDataAdapterInterface::class, ResetUserPasswordDataAdapter::class);
        $this->app->bind(CreateUserDataAdapterInterface::class, CreateUserDataAdapter::class);
        $this->app->bind(DeactivateUserDataAdapterInterface::class, DeactivateUserDataAdapter::class);
        $this->app->bind(FindByIdUserDataAdapterInterface::class, FindByIdUserDataAdapter::class);
        $this->app->bind(ListAllUserDataAdapterInterface::class, ListAllUserDataAdapter::class);
        $this->app->bind(UpdateUserDataAdapterInterface::class, UpdateUserDataAdapter::class);
        $this->app->bind(AuthenticateUserUsecaseInterface::class, AuthenticateUserUsecase::class);
        $this->app->bind(LogoutUserUsecaseInterface::class, LogoutUserUsecase::class);
        $this->app->bind(ResetUserPasswordUsecaseInterface::class, ResetUserPasswordUsecase::class);

        $this->app->bind(CreateUserUsecaseInterface::class, CreateUserUsecase::class);
        $this->app->bind(DeactivateUserUsecaseInterface::class, DeactivateUserUsecase::class);
        $this->app->bind(FindByIdUserUsecaseInterface::class, FindByIdUserUsecase::class);
        $this->app->bind(ListAllUserUsecaseInterface::class, ListAllUserUsecase::class);
        $this->app->bind(UpdateUserUsecaseInterface::class, UpdateUserUsecase::class);
    }

    public function boot(): void
    {
        Auth::viaRequest('access-token', function (Request $request): ?User {
            $accessToken = $request->bearerToken();

            if ($accessToken === null || $accessToken === '') {
                return null;
            }

            $userId = DB::table('user_access_tokens')->where('token', hash('sha256', $accessToken))->value('user_id');

            return $userId === null ? null : User::query()->with('roles')->where('is_active', true)->where('id', $userId)->first();
        });

        Gate::define('manage-users', fn (User $user): bool => $user->role === UserRoleEnum::SUPERUSER);

        RateLimiter::for('auth-login', function (Request $request): Limit {
            $email = $request->input('email');
            $normalizedEmail = is_string($email) ? mb_strtolower(trim($email)) : '';

            return Limit::perMinute(5)->by($normalizedEmail.'|'.$request->ip());
        });
    }
}
