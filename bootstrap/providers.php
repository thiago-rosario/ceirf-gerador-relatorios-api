<?php

use App\Providers\AppServiceProvider;
use src\Modules\Identity\Infra\Provider\IdentityServiceProvider;
use src\Modules\Organization\Infra\Provider\OrganizationServiceProvider;
use src\Modules\Report\Infra\Provider\ReportServiceProvider;

return [
    AppServiceProvider::class,
    IdentityServiceProvider::class,
    OrganizationServiceProvider::class,
    ReportServiceProvider::class,
];
