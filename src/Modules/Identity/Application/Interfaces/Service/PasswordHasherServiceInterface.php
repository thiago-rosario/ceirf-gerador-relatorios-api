<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\Interfaces\Service;

interface PasswordHasherServiceInterface
{
    public function hash(string $password): string;

    public function verify(string $plainPassword, string $hashedPassword): bool;
}
