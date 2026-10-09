<?php

declare(strict_types=1);

namespace src\Modules\Identity\Infra\Repositories;

use src\Modules\Identity\Domain\Entity\RoleEntity;
use src\Modules\Identity\Domain\Entity\UserEntity;
use src\Modules\Identity\Domain\Repository\UserRepositoryInterface;
use src\Modules\Identity\Infra\Repositories\Auth\CreateAccessTokenEloquentRepository;
use src\Modules\Identity\Infra\Repositories\Auth\RevokeAccessTokenEloquentRepository;
use src\Modules\Identity\Infra\Repositories\Commands\CreateUserEloquentRepository;
use src\Modules\Identity\Infra\Repositories\Commands\UpdateUserEloquentRepository;
use src\Modules\Identity\Infra\Repositories\Queries\FindAllUserEloquentRepository;
use src\Modules\Identity\Infra\Repositories\Queries\FindUserByEmailEloquentRepository;
use src\Modules\Identity\Infra\Repositories\Queries\FindUserByIdEloquentRepository;
use src\Modules\Identity\Infra\Repositories\Queries\FindUserByNameEloquentRepository;
use src\Modules\Identity\Infra\Repositories\Queries\GetRolesEloquentRepository;
use src\Modules\Identity\Infra\Repositories\Queries\PaginateUserEloquentRepository;
use src\Modules\Shared\Contract\PaginationInterface;

final readonly class UserEloquentRepository implements UserRepositoryInterface
{
    public function __construct(
        private CreateAccessTokenEloquentRepository $createAccessTokenRepository,
        private RevokeAccessTokenEloquentRepository $revokeAccessTokenRepository,
        private CreateUserEloquentRepository $createUserRepository,
        private FindUserByIdEloquentRepository $findUserByIdRepository,
        private FindUserByEmailEloquentRepository $findUserByEmailRepository,
        private FindUserByNameEloquentRepository $findUserByNameRepository,
        private FindAllUserEloquentRepository $findAllUserRepository,
        private PaginateUserEloquentRepository $paginateUserRepository,
        private UpdateUserEloquentRepository $updateUserRepository,
        private GetRolesEloquentRepository $getRolesRepository,
    ) {}

    public function createAccessToken(UserEntity $user): string
    {
        return $this->createAccessTokenRepository->createAccessToken($user);
    }

    public function revokeAccessToken(string $accessToken): void
    {
        $this->revokeAccessTokenRepository->revokeAccessToken($accessToken);
    }

    public function insert(UserEntity $user): UserEntity
    {
        return $this->createUserRepository->insert($user);
    }

    public function findById(string $id): ?UserEntity
    {
        return $this->findUserByIdRepository->findById($id);
    }

    public function findByEmail(string $email): ?UserEntity
    {
        return $this->findUserByEmailRepository->findByEmail($email);
    }

    public function findByName(string $name): ?UserEntity
    {
        return $this->findUserByNameRepository->findByName($name);
    }

    /**
     * @return list<RoleEntity>
     */
    public function getRoles(): array
    {
        return $this->getRolesRepository->getRoles();
    }

    /**
     * @return array<UserEntity>
     */
    public function findAll(string $filter = '', string $orderBy = 'DESC'): array
    {
        return $this->findAllUserRepository->findAll($filter, $orderBy);
    }

    public function paginate(int $page = 1, int $perPage = 10, string $filter = '', string $orderBy = 'DESC'): PaginationInterface
    {
        return $this->paginateUserRepository->paginate($page, $perPage, $filter, $orderBy);
    }

    public function update(UserEntity $user): UserEntity
    {
        return $this->updateUserRepository->update($user);
    }

    public function changePassword(UserEntity $user, string $previousPasswordHash, string $accessToken): UserEntity
    {
        return $this->updateUserRepository->changePassword($user, $previousPasswordHash, $accessToken);
    }
}
