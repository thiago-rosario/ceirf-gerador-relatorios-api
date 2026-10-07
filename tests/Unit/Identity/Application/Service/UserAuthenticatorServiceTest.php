<?php

declare(strict_types=1);

use Illuminate\Hashing\BcryptHasher;
use src\Modules\Identity\Application\Interfaces\Service\PasswordHasherServiceInterface;
use src\Modules\Identity\Application\Service\UserAuthenticatorService;
use src\Modules\Identity\Domain\Entity\UserEntity;
use src\Modules\Identity\Domain\Repository\UserRepositoryInterface;
use src\Modules\Identity\Domain\ValueObject\EmailValueObject;
use src\Modules\Identity\Infra\Service\PasswordHasherService;

afterEach(function (): void {
    Mockery::close();
});

test('authenticates a matching hash after normalizing the email and preserving password spaces', function (): void {
    $hasher = new PasswordHasherService(new BcryptHasher(['rounds' => 4]));
    $user = new UserEntity(name: 'Ana', email: 'ana@example.com', password: $hasher->hash(' secret '));
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldReceive('findByEmail')->once()->with('ana@example.com')->andReturn($user);
    $authenticator = new UserAuthenticatorService($repository, $hasher);

    $output = $authenticator->authenticate(new EmailValueObject(' ANA@example.com '), ' secret ');

    expect($output)->toBe($user);
});

test('returns null when the provided password does not match the stored hash', function (): void {
    $hasher = new PasswordHasherService(new BcryptHasher(['rounds' => 4]));
    $user = new UserEntity(name: 'Ana', email: 'ana@example.com', password: $hasher->hash('secret'));
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldReceive('findByEmail')->once()->with('ana@example.com')->andReturn($user);
    $authenticator = new UserAuthenticatorService($repository, $hasher);

    $output = $authenticator->authenticate(new EmailValueObject('ana@example.com'), 'wrong-password');

    expect($output)->toBeNull();
});

test('returns null for an unknown user without checking a password', function (): void {
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldReceive('findByEmail')->once()->with('unknown@example.com')->andReturnNull();
    $hasher = Mockery::mock(PasswordHasherServiceInterface::class);
    $hasher->shouldNotReceive('verify');
    $authenticator = new UserAuthenticatorService($repository, $hasher);

    $output = $authenticator->authenticate(new EmailValueObject('unknown@example.com'), 'secret');

    expect($output)->toBeNull();
});

test('preserves a lookup failure without checking a password', function (): void {
    $exception = new RuntimeException('Falha ao consultar o usuário.');
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldReceive('findByEmail')->once()->with('ana@example.com')->andThrow($exception);
    $hasher = Mockery::mock(PasswordHasherServiceInterface::class);
    $hasher->shouldNotReceive('verify');
    $authenticator = new UserAuthenticatorService($repository, $hasher);

    expect(fn () => $authenticator->authenticate(new EmailValueObject('ana@example.com'), 'secret'))->toThrow($exception);
});

test('returns the user or null according to password verification without changing the supplied password', function (bool $isAuthenticated): void {
    $user = new UserEntity(name: 'Ana', email: 'ana@example.com', password: 'stored-hash');
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldReceive('findByEmail')->once()->with('ana@example.com')->andReturn($user);
    $hasher = Mockery::mock(PasswordHasherServiceInterface::class);
    $hasher->shouldReceive('verify')->once()->with(' secret ', 'stored-hash')->andReturn($isAuthenticated);
    $authenticator = new UserAuthenticatorService($repository, $hasher);

    $output = $authenticator->authenticate(new EmailValueObject(' ANA@example.com '), ' secret ');

    expect($output)->toBe($isAuthenticated ? $user : null);
})->with([
    'valid credentials' => true,
    'invalid credentials' => false,
]);

test('preserves a password verification failure', function (): void {
    $exception = new RuntimeException('Falha ao verificar a senha.');
    $user = new UserEntity(name: 'Ana', email: 'ana@example.com', password: 'stored-hash');
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldReceive('findByEmail')->once()->with('ana@example.com')->andReturn($user);
    $hasher = Mockery::mock(PasswordHasherServiceInterface::class);
    $hasher->shouldReceive('verify')->once()->with('secret', 'stored-hash')->andThrow($exception);
    $authenticator = new UserAuthenticatorService($repository, $hasher);

    expect(fn () => $authenticator->authenticate(new EmailValueObject('ana@example.com'), 'secret'))->toThrow($exception);
});
