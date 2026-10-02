<?php

declare(strict_types=1);

namespace src\Identity\Application\Usecase\Auth;

use src\Identity\Application\DTO\Auth\LogoutUserInputDTO;
use src\Identity\Application\Exception\InvalidCredentialsException;
use src\Identity\Application\Interfaces\Usecase\Auth\LogoutUserUsecaseInterface;
use src\Identity\Domain\Repository\UserRepositoryInterface;

class LogoutUserUsecase implements LogoutUserUsecaseInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    ) {}

    public function __invoke(LogoutUserInputDTO $input): void
    {
        if (trim($input->accessToken) === '') {
            throw new InvalidCredentialsException;
        }

        $this->repository->revokeAccessToken($input->accessToken);
    }
}
