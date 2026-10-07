<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\Usecase\Auth;

use src\Modules\Identity\Application\DTO\Auth\LogoutUserInputDTO;
use src\Modules\Identity\Application\Exception\InvalidCredentialsException;
use src\Modules\Identity\Application\Interfaces\Usecase\Auth\LogoutUserUsecaseInterface;
use src\Modules\Identity\Domain\Repository\UserRepositoryInterface;

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
