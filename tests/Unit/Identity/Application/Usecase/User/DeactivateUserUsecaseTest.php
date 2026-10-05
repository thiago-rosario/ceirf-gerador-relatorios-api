<?php

declare(strict_types=1);

use src\Identity\Application\DTO\User\DeactivateUserInputDTO;
use src\Identity\Application\Exception\UserNotFoundException;
use src\Identity\Application\Usecase\User\DeactivateUserUsecase;
use src\Identity\Domain\Entity\UserEntity;
use src\Identity\Domain\Exception\InvalidUserIdException;
use src\Identity\Domain\Repository\UserRepositoryInterface;

afterEach(function (): void {
    Mockery::close();
});

test('persists the deactivation and returns the saved public user data', function (): void {
    $user = new UserEntity(
        id: '550e8400-e29b-41d4-a716-446655440000',
        name: 'Ana',
        email: 'ana@example.com',
        password: 'stored-password-hash',
    );
    $savedUser = new UserEntity(
        id: '550e8400-e29b-41d4-a716-446655440000',
        name: 'Ana Silva',
        email: 'ana@example.com',
        password: 'stored-password-hash',
        isActive: false,
    );
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldReceive('findById')->once()->with('550e8400-e29b-41d4-a716-446655440000')->andReturn($user);
    $repository->shouldReceive('update')->once()->with(Mockery::on(
        fn (UserEntity $userToUpdate): bool => ! $userToUpdate->isActive()
            && $userToUpdate->id()->value() === '550e8400-e29b-41d4-a716-446655440000'
            && $userToUpdate->name() === 'Ana'
            && $userToUpdate->email()->value() === 'ana@example.com'
            && $userToUpdate->password() === 'stored-password-hash',
    ))->andReturn($savedUser);
    $usecase = new DeactivateUserUsecase($repository);

    $output = $usecase(new DeactivateUserInputDTO(id: '550e8400-e29b-41d4-a716-446655440000'));

    expect(get_object_vars($output))->toBe([
        'id' => '550e8400-e29b-41d4-a716-446655440000',
        'name' => 'Ana Silva',
        'email' => 'ana@example.com',
        'active' => false,
    ]);
});

test('rejects an unknown user without attempting an update', function (): void {
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldReceive('findById')->once()->with('550e8400-e29b-41d4-a716-446655440000')->andReturnNull();
    $repository->shouldNotReceive('update');
    $usecase = new DeactivateUserUsecase($repository);

    expect(fn () => $usecase(new DeactivateUserInputDTO(id: '550e8400-e29b-41d4-a716-446655440000')))
        ->toThrow(UserNotFoundException::class, 'Usuário não encontrado.');
});

test('rejects an invalid identifier before accessing the repository', function (string $id): void {
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldNotReceive('findById');
    $repository->shouldNotReceive('update');
    $usecase = new DeactivateUserUsecase($repository);

    expect(fn () => $usecase(new DeactivateUserInputDTO(id: $id)))
        ->toThrow(InvalidUserIdException::class);
})->with([
    'empty' => [''],
    'malformed UUID' => ['invalid-user-id'],
]);

test('keeps an inactive user inactive without changing its modification date', function (): void {
    $user = new UserEntity(
        id: '550e8400-e29b-41d4-a716-446655440000',
        name: 'Ana',
        email: 'ana@example.com',
        password: 'stored-password-hash',
        isActive: false,
        createdAt: '2026-09-30T10:00:00+00:00',
        updatedAt: '2026-10-01T10:00:00+00:00',
    );
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldReceive('findById')->once()->with('550e8400-e29b-41d4-a716-446655440000')->andReturn($user);
    $repository->shouldReceive('update')->once()->with(Mockery::on(
        fn (UserEntity $userToUpdate): bool => ! $userToUpdate->isActive()
            && $userToUpdate->updatedAt()->format(DATE_ATOM) === '2026-10-01T10:00:00+00:00',
    ))->andReturnUsing(fn (UserEntity $userToUpdate): UserEntity => $userToUpdate);
    $usecase = new DeactivateUserUsecase($repository);

    $output = $usecase(new DeactivateUserInputDTO(id: '550e8400-e29b-41d4-a716-446655440000'));

    expect($output->active)->toBeFalse();
});

test('preserves the original user state and propagates a persistence failure', function (): void {
    $user = new UserEntity(
        id: '550e8400-e29b-41d4-a716-446655440000',
        name: 'Ana',
        email: 'ana@example.com',
        password: 'stored-password-hash',
        createdAt: '2026-09-30T10:00:00+00:00',
        updatedAt: '2026-10-01T10:00:00+00:00',
    );
    $exception = new RuntimeException('Falha ao persistir o usuário.');
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldReceive('findById')->once()->with('550e8400-e29b-41d4-a716-446655440000')->andReturn($user);
    $repository->shouldReceive('update')->once()->with(Mockery::on(
        fn (UserEntity $userToUpdate): bool => ! $userToUpdate->isActive(),
    ))->andThrow($exception);
    $usecase = new DeactivateUserUsecase($repository);

    expect(fn () => $usecase(new DeactivateUserInputDTO(id: '550e8400-e29b-41d4-a716-446655440000')))
        ->toThrow($exception);

    expect($user->isActive())->toBeTrue();
    expect($user->updatedAt()->format(DATE_ATOM))->toBe('2026-10-01T10:00:00+00:00');
});
