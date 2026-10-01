<?php

declare(strict_types=1);

use src\Identity\Application\DTO\Auth\AuthenticateUserInputDTO;
use src\Identity\Application\DTO\Auth\AuthenticateUserOutputDTO;
use src\Identity\Application\Exception\InvalidCredentialsException;
use src\Identity\Application\Interfaces\Auth\AuthenticateUserUsecaseInterface;
use src\Identity\Application\Usecase\Auth\AuthenticateUserUsecase;
use src\Identity\Domain\Entity\UserEntity;
use src\Identity\Domain\Enum\UserRoleEnum;
use src\Identity\Domain\Repository\UserRepositoryInterface;

afterEach(function (): void {
    Mockery::close();
});

test('returns the issued access token and public user data for each role', function (UserRoleEnum $role): void {
    $user = new UserEntity(
        id: '550e8400-e29b-41d4-a716-446655440000',
        name: 'Ana Silva',
        email: 'ana@example.com',
        password: 'stored-password-hash',
        role: $role,
    );
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldReceive('authenticate')->once()->with('ana@example.com', ' secret ')->andReturn($user);
    $repository->shouldReceive('createAccessToken')->once()->with($user)->andReturn('issued-access-token');
    $usecase = new AuthenticateUserUsecase($repository);

    $output = $usecase(new AuthenticateUserInputDTO(email: 'ana@example.com', password: ' secret '));

    expect($usecase)->toBeInstanceOf(AuthenticateUserUsecaseInterface::class);
    expect($output)->toBeInstanceOf(AuthenticateUserOutputDTO::class);
    expect($output->accessToken)->toBe('issued-access-token');
    expect(get_object_vars($output->user))->toBe([
        'id' => '550e8400-e29b-41d4-a716-446655440000',
        'name' => 'Ana Silva',
        'email' => 'ana@example.com',
        'role' => $role->value,
    ]);
})->with(UserRoleEnum::cases());

test('rejects invalid credentials without issuing an access token', function (): void {
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldReceive('authenticate')->once()->with('ana@example.com', 'wrong-password')->andReturnNull();
    $repository->shouldNotReceive('createAccessToken');
    $usecase = new AuthenticateUserUsecase($repository);

    expect(fn () => $usecase(new AuthenticateUserInputDTO(email: 'ana@example.com', password: 'wrong-password')))
        ->toThrow(function (InvalidCredentialsException $exception): void {
            expect($exception->getCode())->toBe(1101);
            expect($exception->getMessage())->toBe('Credenciais inválidas.');
        });
});

test('rejects inactive users without issuing an access token', function (): void {
    $user = new UserEntity(name: 'Ana', email: 'ana@example.com', password: 'stored-password-hash', isActive: false);
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldReceive('authenticate')->once()->with('ana@example.com', 'secret')->andReturn($user);
    $repository->shouldNotReceive('createAccessToken');
    $usecase = new AuthenticateUserUsecase($repository);

    expect(fn () => $usecase(new AuthenticateUserInputDTO(email: 'ana@example.com', password: 'secret')))
        ->toThrow(InvalidCredentialsException::class, 'Credenciais inválidas.');
});
