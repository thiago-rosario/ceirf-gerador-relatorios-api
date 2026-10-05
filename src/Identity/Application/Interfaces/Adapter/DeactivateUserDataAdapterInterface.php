<?php

declare(strict_types=1);

namespace src\Identity\Application\Interfaces\Adapter;

use src\Identity\Application\DTO\User\DeactivateUserInputDTO;
use src\Identity\Application\DTO\User\DeactivateUserOutputDTO;

interface DeactivateUserDataAdapterInterface
{
    /**
     * @param  array{id: string}  $data
     */
    public function fromArray(array $data): DeactivateUserInputDTO;

    /**
     * @return array{id: string, name: string, email: string, is_active: bool}
     */
    public function toArray(DeactivateUserOutputDTO $data): array;
}
