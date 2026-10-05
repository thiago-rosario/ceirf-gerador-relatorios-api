<?php

declare(strict_types=1);

namespace src\Identity\Infra\Service;

use Illuminate\Contracts\Hashing\Hasher;
use src\Identity\Application\Interfaces\Service\PasswordHasherServiceInterface;

class PasswordHasherService implements PasswordHasherServiceInterface
{
    public function __construct(
        private readonly Hasher $hasher,
    ) {}

    public function hash(string $password): string
    {
        return $this->hasher->make($password);
    }

    public function verify(string $plainPassword, string $hashedPassword): bool
    {
        return $this->hasher->check($plainPassword, $hashedPassword);
    }
}
