<?php

declare(strict_types=1);

use src\Identity\Application\DTO\Auth\AuthenticateUserInputDTO;
use src\Identity\Application\DTO\Auth\AuthenticateUserOutputDTO;
use src\Identity\Application\Exception\InvalidCredentialsException;
use src\Identity\Application\Interfaces\Service\UserAuthenticatorServiceInterface;
use src\Identity\Application\Interfaces\Usecase\Auth\AuthenticateUserUsecaseInterface;
use src\Identity\Application\Usecase\Auth\AuthenticateUserUsecase;
use src\Identity\Domain\Entity\UserEntity;
use src\Identity\Domain\Enum\UserRoleEnum;
use src\Identity\Domain\Exception\InvalidEmailException;
use src\Identity\Domain\Repository\UserRepositoryInterface;
use src\Identity\Domain\ValueObject\EmailValueObject;

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
    $authenticator = Mockery::mock(UserAuthenticatorServiceInterface::class);
    $authenticator->shouldReceive('authenticate')->once()->with(
        Mockery::on(fn (EmailValueObject $email): bool => $email->value() === 'ana@example.com'),
        ' secret ',
    )->andReturn($user);
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldReceive('createAccessToken')->once()->with($user)->andReturn('issued-access-token');
    $usecase = new AuthenticateUserUsecase($repository, $authenticator);

    $output = $usecase(new AuthenticateUserInputDTO(email: ' ANA@example.com ', password: ' secret '));

    expect($usecase)->toBeInstanceOf(AuthenticateUserUsecaseInterface::class);
    expect($output)->toBeInstanceOf(AuthenticateUserOutputDTO::class);
    expect($output->accessToken)->toBe('issued-access-token');
    expect(get_object_vars($output->user))->toBe([
        'id' => '550e8400-e29b-41d4-a716-446655440000',
        'name' => 'Ana Silva',
        'email' => 'ana@example.com',
        'role' => $role->value,
        'mustChangePassword' => false,
    ]);
})->with(UserRoleEnum::cases());

test('exposes the required password change after authenticating a reset user', function (): void {
    $user = new UserEntity(
        name: 'Ana',
        email: 'ana@example.com',
        password: 'stored-password-hash',
        mustChangePassword: true,
    );
    $authenticator = Mockery::mock(UserAuthenticatorServiceInterface::class);
    $authenticator->shouldReceive('authenticate')->once()->with(
        Mockery::on(fn (EmailValueObject $email): bool => $email->value() === 'ana@example.com'),
        'sspba123',
    )->andReturn($user);
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldReceive('createAccessToken')->once()->with($user)->andReturn('issued-access-token');
    $usecase = new AuthenticateUserUsecase($repository, $authenticator);

    $output = $usecase(new AuthenticateUserInputDTO(email: 'ana@example.com', password: 'sspba123'));

    expect($output->user->mustChangePassword)->toBeTrue();
});

test('rejects invalid credentials without issuing an access token', function (): void {
    $authenticator = Mockery::mock(UserAuthenticatorServiceInterface::class);
    $authenticator->shouldReceive('authenticate')->once()->with(
        Mockery::on(fn (EmailValueObject $email): bool => $email->value() === 'ana@example.com'),
        'wrong-password',
    )->andReturnNull();
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldNotReceive('createAccessToken');
    $usecase = new AuthenticateUserUsecase($repository, $authenticator);

    expect(fn () => $usecase(new AuthenticateUserInputDTO(email: 'ana@example.com', password: 'wrong-password')))
        ->toThrow(function (InvalidCredentialsException $exception): void {
            expect($exception->getCode())->toBe(1101);
            expect($exception->getMessage())->toBe('Credenciais inválidas.');
        });
});

test('rejects inactive users without issuing an access token', function (): void {
    $user = new UserEntity(name: 'Ana', email: 'ana@example.com', password: 'stored-password-hash', isActive: false);
    $authenticator = Mockery::mock(UserAuthenticatorServiceInterface::class);
    $authenticator->shouldReceive('authenticate')->once()->with(
        Mockery::on(fn (EmailValueObject $email): bool => $email->value() === 'ana@example.com'),
        'secret',
    )->andReturn($user);
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldNotReceive('createAccessToken');
    $usecase = new AuthenticateUserUsecase($repository, $authenticator);

    expect(fn () => $usecase(new AuthenticateUserInputDTO(email: 'ana@example.com', password: 'secret')))
        ->toThrow(InvalidCredentialsException::class, 'Credenciais inválidas.');
});

test('rejects an invalid email before authenticating or issuing an access token', function (string $email): void {
    $authenticator = Mockery::mock(UserAuthenticatorServiceInterface::class);
    $authenticator->shouldNotReceive('authenticate');
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldNotReceive('createAccessToken');
    $usecase = new AuthenticateUserUsecase($repository, $authenticator);

    expect(fn () => $usecase(new AuthenticateUserInputDTO(email: $email, password: 'secret')))
        ->toThrow(InvalidEmailException::class);
})->with([
    'empty email' => [''],
    'malformed email' => ['invalid-email'],
]);

test('preserves an authenticator failure without issuing an access token', function (): void {
    $exception = new RuntimeException('Falha ao autenticar o usuário.');
    $authenticator = Mockery::mock(UserAuthenticatorServiceInterface::class);
    $authenticator->shouldReceive('authenticate')->once()->with(
        Mockery::on(fn (EmailValueObject $email): bool => $email->value() === 'ana@example.com'),
        'secret',
    )->andThrow($exception);
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldNotReceive('createAccessToken');
    $usecase = new AuthenticateUserUsecase($repository, $authenticator);

    expect(fn () => $usecase(new AuthenticateUserInputDTO(email: 'ana@example.com', password: 'secret')))
        ->toThrow($exception);
});
