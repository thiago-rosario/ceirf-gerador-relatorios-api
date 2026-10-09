<?php

declare(strict_types=1);

namespace src\Modules\Organization\Infra\Provider;

use Illuminate\Support\ServiceProvider;
use src\Modules\Organization\Application\Interfaces\Adapter\GetCoordinationsDataAdapterInterface;
use src\Modules\Organization\Application\Interfaces\Usecase\GetCoordinationsUsecaseInterface;
use src\Modules\Organization\Application\Usecase\GetCoordinationsUsecase;
use src\Modules\Organization\Domain\Repository\CoordinationRepositoryInterface;
use src\Modules\Organization\Infra\Adapter\GetCoordinationsDataAdapter;
use src\Modules\Organization\Infra\Repositories\CoordinationDatabaseRepository;

class OrganizationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CoordinationRepositoryInterface::class, CoordinationDatabaseRepository::class);

        $this->app->bind(GetCoordinationsDataAdapterInterface::class, GetCoordinationsDataAdapter::class);
        $this->app->bind(GetCoordinationsUsecaseInterface::class, GetCoordinationsUsecase::class);
    }
}
