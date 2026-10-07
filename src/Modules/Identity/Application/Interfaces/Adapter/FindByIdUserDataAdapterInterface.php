<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\Interfaces\Adapter;

use src\Modules\Identity\Application\DTO\User\FindByIdUserInputDTO;
use src\Modules\Identity\Application\DTO\User\FindByIdUserOutputDTO;

interface FindByIdUserDataAdapterInterface
{
    /**
     * @param  array{id?: string, name?: string, email?: string}  $data
     */
    public function fromArray(array $data): FindByIdUserInputDTO;

    /**
     * @return array{id: string, name: string, email: string, role: string, is_active: bool, created_at: string, must_change_password: bool}
     */
    public function toArray(FindByIdUserOutputDTO $data): array;
}
