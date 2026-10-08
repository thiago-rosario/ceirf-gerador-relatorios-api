<?php

declare(strict_types=1);

use Mockery\MockInterface;
use src\Modules\Identity\Domain\Entity\UserEntity;
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
use src\Modules\Identity\Infra\Repositories\UserEloquentRepository;
use src\Modules\Shared\Contract\PaginationInterface;

afterEach(function (): void {
    Mockery::close();
});

/**
 * @return array{repository: UserEloquentRepository, dependencies: array<string, MockInterface>}
 */
function userEloquentRepositoryContext(): array
{
    $dependencies = [
        'createAccessTokenRepository' => Mockery::mock(CreateAccessTokenEloquentRepository::class),
        'revokeAccessTokenRepository' => Mockery::mock(RevokeAccessTokenEloquentRepository::class),
        'createUserRepository' => Mockery::mock(CreateUserEloquentRepository::class),
        'findUserByIdRepository' => Mockery::mock(FindUserByIdEloquentRepository::class),
        'findUserByEmailRepository' => Mockery::mock(FindUserByEmailEloquentRepository::class),
        'findUserByNameRepository' => Mockery::mock(FindUserByNameEloquentRepository::class),
        'findAllUserRepository' => Mockery::mock(FindAllUserEloquentRepository::class),
        'paginateUserRepository' => Mockery::mock(PaginateUserEloquentRepository::class),
        'updateUserRepository' => Mockery::mock(UpdateUserEloquentRepository::class),
        'getRolesRepository' => Mockery::mock(GetRolesEloquentRepository::class),
    ];

    return [
        'repository' => new UserEloquentRepository(...$dependencies),
        'dependencies' => $dependencies,
    ];
}

test('delegates user persistence and returns the persisted entity', function (string $dependency, string $method): void {
    $context = userEloquentRepositoryContext();
    $user = new UserEntity(name: 'Ana', email: 'ana@example.com', password: 'secret');
    $savedUser = clone $user;
    $context['dependencies'][$dependency]->shouldReceive($method)->once()->with($user)->andReturn($savedUser);

    $output = $context['repository']->{$method}($user);

    expect($output)->toBe($savedUser);
})->with([
    'creation' => ['createUserRepository', 'insert'],
    'update' => ['updateUserRepository', 'update'],
]);

test('delegates each lookup and preserves its nullable result', function (string $dependency, string $method, string $criteria, bool $isFound): void {
    $context = userEloquentRepositoryContext();
    $user = $isFound ? new UserEntity(name: 'Ana', email: 'ana@example.com', password: 'secret') : null;
    $context['dependencies'][$dependency]->shouldReceive($method)->once()->with($criteria)->andReturn($user);

    $output = $context['repository']->{$method}($criteria);

    expect($output)->toBe($user);
})->with([
    'identifier' => ['findUserByIdRepository', 'findById', '550e8400-e29b-41d4-a716-446655440000'],
    'email' => ['findUserByEmailRepository', 'findByEmail', 'ana@example.com'],
    'name' => ['findUserByNameRepository', 'findByName', 'Ana'],
])->with([
    'found' => true,
    'not found' => false,
]);

test('returns the access token issued by the token repository', function (): void {
    $context = userEloquentRepositoryContext();
    $user = new UserEntity(name: 'Ana', email: 'ana@example.com', password: 'stored-hash');
    $context['dependencies']['createAccessTokenRepository']->shouldReceive('createAccessToken')
        ->once()->with($user)->andReturn('issued-access-token');

    $output = $context['repository']->createAccessToken($user);

    expect($output)->toBe('issued-access-token');
});

test('delegates revocation of the received access token', function (): void {
    $context = userEloquentRepositoryContext();
    $context['dependencies']['revokeAccessTokenRepository']->shouldReceive('revokeAccessToken')
        ->once()->with('current-access-token');

    $output = $context['repository']->revokeAccessToken('current-access-token');

    expect($output)->toBeNull();
});

test('delegates listing with explicit criteria or the contract defaults', function (array $arguments, string $filter, string $orderBy): void {
    $context = userEloquentRepositoryContext();
    $users = [new UserEntity(name: 'Ana', email: 'ana@example.com', password: 'stored-hash')];
    $context['dependencies']['findAllUserRepository']->shouldReceive('findAll')
        ->once()->with($filter, $orderBy)->andReturn($users);

    $output = $context['repository']->findAll(...$arguments);

    expect($output)->toBe($users);
})->with([
    'explicit criteria' => [['Ana', 'ASC'], 'Ana', 'ASC'],
    'defaults' => [[], '', 'DESC'],
]);

test('delegates pagination and returns the same pagination object', function (array $arguments, int $page, int $perPage, string $filter, string $orderBy): void {
    $context = userEloquentRepositoryContext();
    $pagination = Mockery::mock(PaginationInterface::class);
    $context['dependencies']['paginateUserRepository']->shouldReceive('paginate')
        ->once()->with($page, $perPage, $filter, $orderBy)->andReturn($pagination);

    $output = $context['repository']->paginate(...$arguments);

    expect($output)->toBe($pagination);
})->with([
    'explicit criteria' => [[2, 5, 'Ana', 'ASC'], 2, 5, 'Ana', 'ASC'],
    'defaults' => [[], 1, 10, '', 'DESC'],
]);

test('propagates a repository failure without replacing the exception', function (): void {
    $context = userEloquentRepositoryContext();
    $exception = new RuntimeException('Falha ao persistir o usuário.');
    $user = new UserEntity(name: 'Ana', email: 'ana@example.com', password: 'secret');
    $context['dependencies']['updateUserRepository']->shouldReceive('update')->once()->with($user)->andThrow($exception);

    expect(fn () => $context['repository']->update($user))->toThrow($exception);
});
