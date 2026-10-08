<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\DTO\User;

readonly class GetRolesOutputDTO
{
    /**
     * @param  list<RoleDataDTO>  $roles
     */
    public function __construct(
        public array $roles,
    ) {}
}
