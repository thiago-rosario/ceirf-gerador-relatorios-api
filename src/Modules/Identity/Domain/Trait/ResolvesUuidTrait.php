<?php

declare(strict_types=1);

namespace src\Modules\Identity\Domain\Trait;

use src\Modules\Shared\Resolver\UuidResolver;

trait ResolvesUuidTrait
{
    private function resolveUuid(UuidResolver|string|null $id): UuidResolver
    {
        if ($id instanceof UuidResolver) {
            return $id;
        }

        return $id === null ? UuidResolver::random() : new UuidResolver($id);
    }
}
