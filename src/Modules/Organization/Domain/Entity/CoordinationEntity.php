<?php

declare(strict_types=1);

namespace src\Modules\Organization\Domain\Entity;

class CoordinationEntity
{
    public function __construct(
        private int $id,
        private string $code,
        private string $name,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function name(): string
    {
        return $this->name;
    }
}
