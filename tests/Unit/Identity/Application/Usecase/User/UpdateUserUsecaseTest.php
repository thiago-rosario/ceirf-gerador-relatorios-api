<?php

declare(strict_types=1);

use src\Modules\Identity\Application\DTO\User\UpdateUserInputDTO;
use src\Modules\Identity\Application\Interfaces\Service\PasswordHasherServiceInterface;
use src\Modules\Identity\Application\Usecase\User\UpdateUserUsecase;
use src\Modules\Identity\Domain\Entity\UserEntity;
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

test('updates reject missing and inactive replacement coordinations before persistence without an HTTP request', function (bool $exists): void {
    $user = new UserEntity(name: 'Ana Silva', email: 'ana@example.com', password: 'password', coordinationId: 10);
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldReceive('findById')->once()->with($user->id()->value())->andReturn($user);
    $repository->shouldNotReceive('update');
    $hasher = Mockery::mock(PasswordHasherServiceInterface::class);
    $hasher->shouldNotReceive('hash');
    $coordinations = Mockery::mock(CoordinationRepositoryInterface::class);
    $coordinations->shouldReceive('findById')->once()->with(20)
        ->andReturn($exists ? new CoordinationEntity(id: 20, code: 'COTEC', name: 'Coordenação técnica', isActive: false) : null);
    $usecase = new UpdateUserUsecase($repository, $hasher, $coordinations);
    $input = new UpdateUserInputDTO(id: $user->id()->value(), name: 'Changed User', coordinationId: 20);

    expect(fn () => $usecase($input))->toThrow(InvalidUserCoordinationException::class);
    expect($user->name())->toBe('Ana Silva');
    expect($user->coordinationId())->toBe(10);
})->with(['missing' => [false], 'inactive' => [true]]);

test('promoting an unassigned viewer to a writer validates the final role without an HTTP request', function (UserRoleEnum $role): void {
    $user = new UserEntity(name: 'Ana Silva', email: 'ana@example.com', password: 'password', role: UserRoleEnum::VIEWER);
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldReceive('findById')->once()->with($user->id()->value())->andReturn($user);
    $repository->shouldNotReceive('update');
    $hasher = Mockery::mock(PasswordHasherServiceInterface::class);
    $hasher->shouldNotReceive('hash');
    $coordinations = Mockery::mock(CoordinationRepositoryInterface::class);
    $coordinations->shouldNotReceive('findById');
    $usecase = new UpdateUserUsecase($repository, $hasher, $coordinations);

    expect(fn () => $usecase(new UpdateUserInputDTO(id: $user->id()->value(), role: $role)))
        ->toThrow(InvalidUserCoordinationException::class);
    expect($user->role())->toBe(UserRoleEnum::VIEWER);
    expect($user->coordinationId())->toBeNull();
})->with(['operator' => [UserRoleEnum::OPERATOR], 'reviewer' => [UserRoleEnum::REVIEWER]]);

test('a directly constructed update DTO replaces the coordination without a transport presence flag', function (): void {
    $user = new UserEntity(name: 'Ana Silva', email: 'ana@example.com', password: 'password', coordinationId: 10);
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldReceive('findById')->once()->with($user->id()->value())->andReturn($user);
    $repository->shouldReceive('update')->once()->with(Mockery::on(
        fn (UserEntity $updatedUser): bool => $updatedUser->coordinationId() === 20 && $updatedUser->id()->value() === $user->id()->value(),
    ))->andReturnUsing(fn (UserEntity $updatedUser): UserEntity => $updatedUser);
    $hasher = Mockery::mock(PasswordHasherServiceInterface::class);
    $hasher->shouldNotReceive('hash');
    $coordinations = Mockery::mock(CoordinationRepositoryInterface::class);
    $coordinations->shouldReceive('findById')->once()->with(20)
        ->andReturn(new CoordinationEntity(id: 20, code: 'COTEC', name: 'Coordenação técnica'));
    $usecase = new UpdateUserUsecase($repository, $hasher, $coordinations);

    $output = $usecase(new UpdateUserInputDTO(id: $user->id()->value(), coordinationId: 20));

    expect($output->coordinationId)->toBe(20);
    expect($output->coordination?->code)->toBe('COTEC');
    expect($user->coordinationId())->toBe(10);
});
