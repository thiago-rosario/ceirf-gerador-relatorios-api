<?php

declare(strict_types=1);

use src\Modules\Identity\Application\DTO\User\CreateUserInputDTO;
use src\Modules\Identity\Application\Interfaces\Service\PasswordHasherServiceInterface;
use src\Modules\Identity\Application\Usecase\User\CreateUserUsecase;
use src\Modules\Identity\Domain\Enum\UserRoleEnum;
use src\Modules\Identity\Domain\Exception\InvalidUserCoordinationException;
use src\Modules\Identity\Domain\Repository\UserRepositoryInterface;
use src\Modules\Organization\Domain\Entity\CoordinationEntity;
use src\Modules\Organization\Domain\Repository\CoordinationRepositoryInterface;

afterEach(function (): void {
    Mockery::close();
});

test('example', function () {
    expect(true)->toBeTrue();
});

test('writers require a coordination before hashing or persistence without an HTTP request', function (UserRoleEnum $role): void {
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldNotReceive('insert');
    $hasher = Mockery::mock(PasswordHasherServiceInterface::class);
    $hasher->shouldNotReceive('hash');
    $coordinations = Mockery::mock(CoordinationRepositoryInterface::class);
    $coordinations->shouldNotReceive('findById');
    $usecase = new CreateUserUsecase($repository, $hasher, $coordinations);
    $input = new CreateUserInputDTO(name: 'Ana Silva', email: 'ana@example.com', password: 'password', role: $role);

    expect(fn () => $usecase($input))->toThrow(InvalidUserCoordinationException::class);
})->with(['operator' => [UserRoleEnum::OPERATOR], 'reviewer' => [UserRoleEnum::REVIEWER]]);

test('creation rejects missing and inactive coordinations from the repository without an HTTP request', function (bool $exists): void {
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldNotReceive('insert');
    $hasher = Mockery::mock(PasswordHasherServiceInterface::class);
    $hasher->shouldNotReceive('hash');
    $coordinations = Mockery::mock(CoordinationRepositoryInterface::class);
    $coordinations->shouldReceive('findById')->once()->with(10)
        ->andReturn($exists ? new CoordinationEntity(id: 10, code: 'COTEC', name: 'Coordenação técnica', isActive: false) : null);
    $usecase = new CreateUserUsecase($repository, $hasher, $coordinations);
    $input = new CreateUserInputDTO(name: 'Ana Silva', email: 'ana@example.com', password: 'password', coordinationId: 10);

    expect(fn () => $usecase($input))->toThrow(InvalidUserCoordinationException::class);
})->with(['missing' => [false], 'inactive' => [true]]);

test('superusers cannot receive a coordination without an HTTP request', function (): void {
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldNotReceive('insert');
    $hasher = Mockery::mock(PasswordHasherServiceInterface::class);
    $hasher->shouldNotReceive('hash');
    $coordinations = Mockery::mock(CoordinationRepositoryInterface::class);
    $coordinations->shouldReceive('findById')->once()->with(10)
        ->andReturn(new CoordinationEntity(id: 10, code: 'COTEC', name: 'Coordenação técnica'));
    $usecase = new CreateUserUsecase($repository, $hasher, $coordinations);
    $input = new CreateUserInputDTO(
        name: 'Ana Silva', email: 'ana@example.com', password: 'password', role: UserRoleEnum::SUPERUSER, coordinationId: 10,
    );

    expect(fn () => $usecase($input))->toThrow(InvalidUserCoordinationException::class);
});
