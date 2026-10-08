<?php

declare(strict_types=1);

namespace src\Modules\Identity\Domain\Repository;

use src\Modules\Identity\Domain\Entity\RoleEntity;
use src\Modules\Identity\Domain\Entity\UserEntity;
use src\Modules\Shared\Contract\PaginationInterface;

interface UserRepositoryInterface
{
    public function createAccessToken(UserEntity $user): string;

    public function revokeAccessToken(string $accessToken): void;

    public function insert(UserEntity $user): UserEntity;

    public function findById(string $id): ?UserEntity;

    public function findByEmail(string $email): ?UserEntity;

    public function findByName(string $name): ?UserEntity;

    /**
     * @return list<RoleEntity>
     */
    public function getRoles(): array;

    /**
     * @return array<UserEntity>
     */
    public function findAll(string $filter = '', string $orderBy = 'DESC'): array;

    public function paginate(int $page = 1, int $perPage = 10, string $filter = '', string $orderBy = 'DESC'): PaginationInterface;

    public function update(UserEntity $user): UserEntity;
}
