<?php

declare(strict_types=1);

namespace src\Identity\Application\DTO\User;

readonly class ListAllUserOutputDTO
{
    /**
     * @param list<FindByIdUserOutputDTO> $users
     */
    public function __construct(
        public array $users,
    ) {}
}
