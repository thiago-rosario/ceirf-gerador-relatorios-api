<?php

declare(strict_types=1);

use src\Modules\Identity\Application\DTO\Auth\ResetUserPasswordInputDTO;
use src\Modules\Identity\Application\Exception\UserNotFoundException;
use src\Modules\Identity\Application\Interfaces\Service\PasswordHasherServiceInterface;
use src\Modules\Identity\Application\Usecase\Auth\ResetUserPasswordUsecase;
use src\Modules\Identity\Domain\Entity\UserEntity;
use src\Modules\Identity\Domain\Enum\UserRoleEnum;
use src\Modules\Identity\Domain\Exception\InvalidUserIdException;
use src\Modules\Identity\Domain\Repository\UserRepositoryInterface;

afterEach(function (): void {
    Mockery::close();
});

test('persists the hashed default password and requires a change without mutating the loaded user', function (): void {
    $user = new UserEntity(
        id: '550e8400-e29b-41d4-a716-446655440000',
        name: 'Ana Silva',
        email: 'ana@example.com',
        password: 'previous-password-hash',
        isActive: false,
        role: UserRoleEnum::REVIEWER,
        createdAt: '2000-01-01 00:00:00',
        updatedAt: '2000-01-02 00:00:00',
    );
    $hasher = Mockery::mock(PasswordHasherServiceInterface::class);
    $hasher->shouldReceive('hash')->once()->with('sspba123')->andReturn('hashed-default-password');
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldReceive('findById')->once()->with('550e8400-e29b-41d4-a716-446655440000')->andReturn($user);
    $repository->shouldReceive('update')->once()->with(Mockery::on(
        fn (UserEntity $userToReset): bool => $userToReset !== $user
            && $userToReset->id()->value() === '550e8400-e29b-41d4-a716-446655440000'
            && $userToReset->name() === 'Ana Silva'
            && $userToReset->email()->value() === 'ana@example.com'
            && $userToReset->password() === 'hashed-default-password'
            && ! $userToReset->isActive()
            && $userToReset->role() === UserRoleEnum::REVIEWER
            && $userToReset->mustChangePassword()
            && $userToReset->createdAt()->format('Y-m-d H:i:s') === '2000-01-01 00:00:00'
            && $userToReset->updatedAt() !== $user->updatedAt(),
    ))->andReturnUsing(fn (UserEntity $updatedUser): UserEntity => $updatedUser);
    $usecase = new ResetUserPasswordUsecase($repository, $hasher);

    $output = $usecase(new ResetUserPasswordInputDTO(id: '550e8400-e29b-41d4-a716-446655440000'));

    expect(get_object_vars($output))->toBe([
        'id' => '550e8400-e29b-41d4-a716-446655440000',
        'mustChangePassword' => true,
    ]);
    expect($user->password())->toBe('previous-password-hash');
    expect($user->mustChangePassword())->toBeFalse();
    expect($user->updatedAt()->format('Y-m-d H:i:s'))->toBe('2000-01-02 00:00:00');
});

test('rejects an invalid identifier before loading or changing a password', function (string $id): void {
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldNotReceive('findById');
    $repository->shouldNotReceive('update');
    $hasher = Mockery::mock(PasswordHasherServiceInterface::class);
    $hasher->shouldNotReceive('hash');
    $usecase = new ResetUserPasswordUsecase($repository, $hasher);

    expect(fn () => $usecase(new ResetUserPasswordInputDTO(id: $id)))
        ->toThrow(InvalidUserIdException::class);
})->with([
    'empty identifier' => [''],
    'malformed identifier' => ['not-a-uuid'],
]);

test('rejects a missing user before hashing or persisting a password', function (): void {
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldReceive('findById')->once()->with('550e8400-e29b-41d4-a716-446655440000')->andReturnNull();
    $repository->shouldNotReceive('update');
    $hasher = Mockery::mock(PasswordHasherServiceInterface::class);
    $hasher->shouldNotReceive('hash');
    $usecase = new ResetUserPasswordUsecase($repository, $hasher);

    expect(fn () => $usecase(new ResetUserPasswordInputDTO(id: '550e8400-e29b-41d4-a716-446655440000')))
        ->toThrow(UserNotFoundException::class, 'Usuário não encontrado.');
});

test('preserves a lookup failure without hashing or persisting a password', function (): void {
    $exception = new RuntimeException('Falha ao buscar o usuário.');
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldReceive('findById')->once()->with('550e8400-e29b-41d4-a716-446655440000')->andThrow($exception);
    $repository->shouldNotReceive('update');
    $hasher = Mockery::mock(PasswordHasherServiceInterface::class);
    $hasher->shouldNotReceive('hash');
    $usecase = new ResetUserPasswordUsecase($repository, $hasher);

    expect(fn () => $usecase(new ResetUserPasswordInputDTO(id: '550e8400-e29b-41d4-a716-446655440000')))
        ->toThrow($exception);
});

test('preserves a hashing failure without persisting or mutating the loaded user', function (): void {
    $user = new UserEntity(
        id: '550e8400-e29b-41d4-a716-446655440000',
        name: 'Ana',
        email: 'ana@example.com',
        password: 'previous-password-hash',
        updatedAt: '2000-01-02 00:00:00',
    );
    $exception = new RuntimeException('Falha ao gerar o hash.');
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldReceive('findById')->once()->with('550e8400-e29b-41d4-a716-446655440000')->andReturn($user);
    $repository->shouldNotReceive('update');
    $hasher = Mockery::mock(PasswordHasherServiceInterface::class);
    $hasher->shouldReceive('hash')->once()->with('sspba123')->andThrow($exception);
    $usecase = new ResetUserPasswordUsecase($repository, $hasher);

    expect(fn () => $usecase(new ResetUserPasswordInputDTO(id: '550e8400-e29b-41d4-a716-446655440000')))
        ->toThrow($exception);
    expect($user->password())->toBe('previous-password-hash');
    expect($user->mustChangePassword())->toBeFalse();
    expect($user->updatedAt()->format('Y-m-d H:i:s'))->toBe('2000-01-02 00:00:00');
});

test('preserves a persistence failure without mutating the loaded user', function (): void {
    $user = new UserEntity(
        id: '550e8400-e29b-41d4-a716-446655440000',
        name: 'Ana',
        email: 'ana@example.com',
        password: 'previous-password-hash',
        updatedAt: '2000-01-02 00:00:00',
    );
    $exception = new RuntimeException('Falha ao persistir a senha.');
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldReceive('findById')->once()->with('550e8400-e29b-41d4-a716-446655440000')->andReturn($user);
    $repository->shouldReceive('update')->once()->with(Mockery::on(
        fn (UserEntity $userToReset): bool => $userToReset !== $user
            && $userToReset->password() === 'hashed-default-password'
            && $userToReset->mustChangePassword(),
    ))->andThrow($exception);
    $hasher = Mockery::mock(PasswordHasherServiceInterface::class);
    $hasher->shouldReceive('hash')->once()->with('sspba123')->andReturn('hashed-default-password');
    $usecase = new ResetUserPasswordUsecase($repository, $hasher);

    expect(fn () => $usecase(new ResetUserPasswordInputDTO(id: '550e8400-e29b-41d4-a716-446655440000')))
        ->toThrow($exception);
    expect($user->password())->toBe('previous-password-hash');
    expect($user->mustChangePassword())->toBeFalse();
    expect($user->updatedAt()->format('Y-m-d H:i:s'))->toBe('2000-01-02 00:00:00');
});
