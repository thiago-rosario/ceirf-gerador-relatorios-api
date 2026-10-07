<?php

declare(strict_types=1);

namespace src\Modules\Identity\Domain\Validation;

use src\Modules\Identity\Domain\Entity\UserEntity;
use src\Modules\Identity\Domain\Exception\UserNameCannotBeEmptyException;
use src\Modules\Identity\Domain\Exception\UserPasswordCannotBeEmptyException;

final class UserValidation
{
    public static function validate(UserEntity $user): void
    {
        self::validateName($user->name());
        self::validatePassword($user->password());
    }

    public static function validateName(string $name): void
    {
        if (trim($name) === '' || preg_match('/^\s+$/u', $name) === 1) {
            throw new UserNameCannotBeEmptyException;
        }
    }

    /**
     * Apenas a string vazia é rejeitada; espaços fazem parte do valor da senha.
     */
    public static function validatePassword(string $password): void
    {
        if ($password === '') {
            throw new UserPasswordCannotBeEmptyException;
        }
    }
}
