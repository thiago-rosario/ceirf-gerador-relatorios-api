<?php

declare(strict_types=1);

use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Hashing\BcryptHasher;
use src\Identity\Application\Interfaces\Service\PasswordHasherServiceInterface;
use src\Identity\Infra\Service\PasswordHasherService;

afterEach(function (): void {
    Mockery::close();
});

test('hashes the complete password including spaces', function (): void {
    $service = new PasswordHasherService(new BcryptHasher(['rounds' => 4]));

    $hash = $service->hash(' secret ');

    expect($service)->toBeInstanceOf(PasswordHasherServiceInterface::class);
    expect(password_verify(' secret ', $hash))->toBeTrue();
    expect(password_verify('secret', $hash))->toBeFalse();
});

test('verifies the provided password against a stored hash', function (string $password, bool $isValid): void {
    $hasher = new BcryptHasher(['rounds' => 4]);
    $hash = $hasher->make(' secret ');
    $service = new PasswordHasherService($hasher);

    $output = $service->verify($password, $hash);

    expect($output)->toBe($isValid);
})->with([
    'matching password' => [' secret ', true],
    'different password' => ['wrong-password', false],
    'missing spaces' => ['secret', false],
]);

test('preserves a hashing failure from the configured driver', function (): void {
    $exception = new RuntimeException('Falha ao gerar o hash.');
    $hasher = Mockery::mock(Hasher::class);
    $hasher->shouldReceive('make')->once()->with('secret')->andThrow($exception);
    $service = new PasswordHasherService($hasher);

    expect(fn () => $service->hash('secret'))->toThrow($exception);
});

test('preserves a verification failure from the configured driver', function (): void {
    $exception = new RuntimeException('Falha ao verificar o hash.');
    $hasher = Mockery::mock(Hasher::class);
    $hasher->shouldReceive('check')->once()->with('secret', 'stored-hash')->andThrow($exception);
    $service = new PasswordHasherService($hasher);

    expect(fn () => $service->verify('secret', 'stored-hash'))->toThrow($exception);
});
