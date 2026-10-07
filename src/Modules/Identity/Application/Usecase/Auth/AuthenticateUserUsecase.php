<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\Usecase\Auth;

use src\Modules\Identity\Application\DTO\Auth\AuthenticateUserInputDTO;
use src\Modules\Identity\Application\DTO\Auth\AuthenticateUserOutputDTO;
use src\Modules\Identity\Application\DTO\User\UserDataDTO;
use src\Modules\Identity\Application\Exception\InvalidCredentialsException;
use src\Modules\Identity\Application\Interfaces\Service\UserAuthenticatorServiceInterface;
use src\Modules\Identity\Application\Interfaces\Usecase\Auth\AuthenticateUserUsecaseInterface;
use src\Modules\Identity\Domain\Repository\UserRepositoryInterface;
use src\Modules\Identity\Domain\ValueObject\EmailValueObject;

class AuthenticateUserUsecase implements AuthenticateUserUsecaseInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
        private readonly UserAuthenticatorServiceInterface $service,
    ) {}

    public function __invoke(AuthenticateUserInputDTO $input): AuthenticateUserOutputDTO
    {
        $email = new EmailValueObject($input->email);

        $user = $this->service->authenticate($email, $input->password);

        if ($user === null || ! $user->isActive()) {
            throw new InvalidCredentialsException;
        }

        $userData = new UserDataDTO(
            id: $user->id()->value(),
            name: $user->name(),
            email: $user->email()->value(),
            role: $user->role()->value,
            mustChangePassword: $user->mustChangePassword(),
        );

        $accessToken = $this->repository->createAccessToken($user);

        return new AuthenticateUserOutputDTO(
            accessToken: $accessToken,
            user: $userData,
        );
    }
}
