<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\DTO\User;

readonly class FindByIdUserInputDTO
{
    /**
     * Informe exatamente um critério para buscar um único usuário.
     */
    public function __construct(
        public ?string $id = null,
        public ?string $name = null,
        public ?string $email = null,
    ) {}
}
