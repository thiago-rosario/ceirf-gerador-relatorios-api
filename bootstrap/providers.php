<?php

use App\Providers\AppServiceProvider;
use src\Modules\Identity\Infra\Provider\IdentityServiceProvider;

return [
    AppServiceProvider::class,
    IdentityServiceProvider::class,
];
