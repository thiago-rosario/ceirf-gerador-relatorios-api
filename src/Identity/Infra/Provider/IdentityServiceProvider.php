<?php

declare(strict_types=1);

namespace src\Identity\Infra\Provider;

use Illuminate\Support\ServiceProvider;
use src\Identity\Application\Interfaces\Service\PasswordHasherServiceInterface;
use src\Identity\Application\Interfaces\Service\UserAuthenticatorServiceInterface;
use src\Identity\Application\Interfaces\Usecase\Auth\AuthenticateUserUsecaseInterface;
use src\Identity\Application\Interfaces\Usecase\Auth\LogoutUserUsecaseInterface;
use src\Identity\Application\Interfaces\Usecase\User\CreateUserUsecaseInterface;
use src\Identity\Application\Interfaces\Usecase\User\DeactivateUserUsecaseInterface;
use src\Identity\Application\Interfaces\Usecase\User\FindByIdUserUsecaseInterface;
use src\Identity\Application\Interfaces\Usecase\User\ListAllUserUsecaseInterface;
use src\Identity\Application\Interfaces\Usecase\User\UpdateUserUsecaseInterface;
use src\Identity\Application\Service\UserAuthenticatorService;
use src\Identity\Application\Usecase\Auth\AuthenticateUserUsecase;
use src\Identity\Application\Usecase\Auth\LogoutUserUsecase;
use src\Identity\Application\Usecase\User\CreateUserUsecase;
use src\Identity\Application\Usecase\User\DeactivateUserUsecase;
use src\Identity\Application\Usecase\User\FindByIdUserUsecase;
use src\Identity\Application\Usecase\User\ListAllUserUsecase;
use src\Identity\Application\Usecase\User\UpdateUserUsecase;
use src\Identity\Domain\Repository\UserRepositoryInterface;
use src\Identity\Infra\Repositories\UserEloquentRepository;
use src\Identity\Infra\Service\PasswordHasherService;

class IdentityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserEloquentRepository::class);

        $this->app->bind(PasswordHasherServiceInterface::class, PasswordHasherService::class);
        $this->app->bind(UserAuthenticatorServiceInterface::class, UserAuthenticatorService::class);

        $this->app->bind(AuthenticateUserUsecaseInterface::class, AuthenticateUserUsecase::class);
        $this->app->bind(LogoutUserUsecaseInterface::class, LogoutUserUsecase::class);

        $this->app->bind(CreateUserUsecaseInterface::class, CreateUserUsecase::class);
        $this->app->bind(DeactivateUserUsecaseInterface::class, DeactivateUserUsecase::class);
        $this->app->bind(FindByIdUserUsecaseInterface::class, FindByIdUserUsecase::class);
        $this->app->bind(ListAllUserUsecaseInterface::class, ListAllUserUsecase::class);
        $this->app->bind(UpdateUserUsecaseInterface::class, UpdateUserUsecase::class);
    }

    public function boot(): void {}
}
