<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\Interfaces\Adapter;

use src\Modules\Identity\Application\DTO\Auth\ResetUserPasswordInputDTO;
use src\Modules\Identity\Application\DTO\Auth\ResetUserPasswordOutputDTO;

interface ResetUserPasswordDataAdapterInterface
{
    /**
     * @param  array{id: string}  $data
     */
    public function fromArray(array $data): ResetUserPasswordInputDTO;

    /**
     * @return array{id: string, must_change_password: bool}
     */
    public function toArray(ResetUserPasswordOutputDTO $data): array;
}
