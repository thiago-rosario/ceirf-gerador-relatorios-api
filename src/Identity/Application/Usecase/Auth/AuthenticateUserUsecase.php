<?php

declare(strict_types=1);

namespace src\Identity\Application\Usecase\Auth;

use src\Identity\Application\DTO\Auth\AuthenticateUserInputDTO;
use src\Identity\Application\DTO\Auth\AuthenticateUserOutputDTO;
use src\Identity\Application\DTO\User\UserDataDTO;
use src\Identity\Application\Exception\InvalidCredentialsException;
use src\Identity\Application\Interfaces\Usecase\Auth\AuthenticateUserUsecaseInterface;
use src\Identity\Domain\Repository\UserRepositoryInterface;

class AuthenticateUserUsecase implements AuthenticateUserUsecaseInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    ) {}

    public function __invoke(AuthenticateUserInputDTO $input): AuthenticateUserOutputDTO
    {
        $user = $this->repository->authenticate($input->email, $input->password);

        if ($user === null || ! $user->isActive()) {
            throw new InvalidCredentialsException;
        }

        $userData = new UserDataDTO(
            id: $user->id()->value(),
            name: $user->name(),
            email: $user->email()->value(),
            role: $user->role()->value,
        );

        $accessToken = $this->repository->createAccessToken($user);

        return new AuthenticateUserOutputDTO(
            accessToken: $accessToken,
            user: $userData,
        );
    }
}
