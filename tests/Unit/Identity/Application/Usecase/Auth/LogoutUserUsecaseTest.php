<?php

declare(strict_types=1);

use src\Modules\Identity\Application\DTO\Auth\LogoutUserInputDTO;
use src\Modules\Identity\Application\Exception\InvalidCredentialsException;
use src\Modules\Identity\Application\Interfaces\Usecase\Auth\LogoutUserUsecaseInterface;
use src\Modules\Identity\Application\Usecase\Auth\LogoutUserUsecase;
use src\Modules\Identity\Domain\Repository\UserRepositoryInterface;

afterEach(function (): void {
    Mockery::close();
});

test('revokes only the access token received for logout', function (): void {
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldReceive('revokeAccessToken')->once()->with('current-access-token');
    $repository->shouldNotReceive('findByEmail');
    $repository->shouldNotReceive('createAccessToken');
    $usecase = new LogoutUserUsecase($repository);

    $output = $usecase(new LogoutUserInputDTO(accessToken: 'current-access-token'));

    expect($usecase)->toBeInstanceOf(LogoutUserUsecaseInterface::class);
    expect($output)->toBeNull();
});

test('rejects an empty access token without attempting revocation', function (string $accessToken): void {
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldNotReceive('revokeAccessToken');
    $usecase = new LogoutUserUsecase($repository);

    expect(fn () => $usecase(new LogoutUserInputDTO(accessToken: $accessToken)))
        ->toThrow(InvalidCredentialsException::class, 'Credenciais inválidas.');
})->with([
    'empty' => [''],
    'spaces' => ['   '],
    'line breaks' => ["\t\n"],
]);

test('preserves the authentication error when the repository rejects the token', function (): void {
    $exception = new InvalidCredentialsException;
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $repository->shouldReceive('revokeAccessToken')->once()->with('invalid-access-token')->andThrow($exception);
    $usecase = new LogoutUserUsecase($repository);

    expect(fn () => $usecase(new LogoutUserInputDTO(accessToken: 'invalid-access-token')))->toThrow($exception);
});
