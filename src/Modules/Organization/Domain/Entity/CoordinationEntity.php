<?php

declare(strict_types=1);

namespace src\Modules\Organization\Domain\Entity;

class CoordinationEntity
{
    public function __construct(
        private int $id,
        private string $code,
        private string $name,
        private bool $isActive = true,
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

    public function isActive(): bool
    {
        return $this->isActive;
    }
}
