<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\Usecase\Auth;

use src\Modules\Identity\Application\DTO\Auth\ChangeUserPasswordInputDTO;
use src\Modules\Identity\Application\DTO\User\FindByIdUserInputDTO;
use src\Modules\Identity\Application\DTO\User\FindByIdUserOutputDTO;
use src\Modules\Identity\Application\Exception\PasswordChangeRejectedException;
use src\Modules\Identity\Application\Exception\UserNotFoundException;
use src\Modules\Identity\Application\Interfaces\Service\PasswordHasherServiceInterface;
use src\Modules\Identity\Application\Interfaces\Usecase\Auth\ChangeUserPasswordUsecaseInterface;
use src\Modules\Identity\Application\Interfaces\Usecase\User\FindByIdUserUsecaseInterface;
use src\Modules\Identity\Domain\Repository\UserRepositoryInterface;

class ChangeUserPasswordUsecase implements ChangeUserPasswordUsecaseInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
        private readonly PasswordHasherServiceInterface $hasher,
        private readonly FindByIdUserUsecaseInterface $findUser,
    ) {}

    public function __invoke(ChangeUserPasswordInputDTO $input): FindByIdUserOutputDTO
    {
        $user = $this->repository->findById($input->id);

        if ($user === null || ! $user->isActive()) {
            throw new UserNotFoundException;
        }

        if (! $this->hasher->verify($input->currentPassword, $user->password())) {
            throw new PasswordChangeRejectedException('current_password', 'A senha atual está incorreta.');
        }

        if ($this->hasher->verify($input->password, $user->password())) {
            throw new PasswordChangeRejectedException('password', 'A nova senha deve ser diferente da senha atual.');
        }

        $userToChange = clone $user;
        $userToChange->changePassword($this->hasher->hash($input->password));
        $this->repository->changePassword($userToChange, $user->password(), $input->accessToken);

        return ($this->findUser)(new FindByIdUserInputDTO(id: $input->id));
    }
}
