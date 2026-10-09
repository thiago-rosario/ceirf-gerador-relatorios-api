<?php

declare(strict_types=1);

namespace src\Modules\Identity\Domain\Validation;

use src\Modules\Identity\Domain\Entity\UserEntity;
use src\Modules\Identity\Domain\Enum\UserRoleEnum;
use src\Modules\Identity\Domain\Exception\InvalidUserCoordinationException;
use src\Modules\Identity\Domain\Exception\UserNameCannotBeEmptyException;
use src\Modules\Identity\Domain\Exception\UserPasswordCannotBeEmptyException;
use src\Modules\Organization\Domain\Entity\CoordinationEntity;

final class UserValidation
{
    public static function validate(UserEntity $user): void
    {
        self::validateName($user->name());
        self::validatePassword($user->password());
        self::validateCoordinationId($user->coordinationId());
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

    public static function validateCoordinationId(?int $coordinationId): void
    {
        if ($coordinationId !== null && $coordinationId < 1) {
            throw new InvalidUserCoordinationException;
        }
    }

    public static function validateCoordination(
        UserRoleEnum $role,
        ?int $coordinationId,
        ?CoordinationEntity $coordination,
        bool $requireActive = true,
    ): void {
        self::validateCoordinationId($coordinationId);

        if ($role === UserRoleEnum::SUPERUSER && $coordinationId !== null) {
            throw new InvalidUserCoordinationException('Superusuários não devem possuir coordenação vinculada.');
        }

        if (in_array($role, [UserRoleEnum::OPERATOR, UserRoleEnum::REVIEWER], true) && $coordinationId === null) {
            throw new InvalidUserCoordinationException('A coordenação é obrigatória para operadores e revisores.');
        }

        if ($coordinationId !== null && ($coordination === null || $coordination->id() !== $coordinationId)) {
            throw new InvalidUserCoordinationException('A coordenação selecionada não existe.');
        }

        if ($requireActive && $coordination !== null && ! $coordination->isActive()) {
            throw new InvalidUserCoordinationException('A coordenação selecionada está inativa.');
        }
    }
}
