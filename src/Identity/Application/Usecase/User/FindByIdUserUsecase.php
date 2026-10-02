<?php

declare(strict_types=1);

namespace src\Identity\Application\Usecase\User;

use src\Identity\Application\DTO\User\FindByIdUserInputDTO;
use src\Identity\Application\DTO\User\FindByIdUserOutputDTO;
use src\Identity\Application\Exception\InvalidUserSearchException;
use src\Identity\Application\Exception\UserNotFoundException;
use src\Identity\Application\Interfaces\Usecase\User\FindByIdUserUsecaseInterface;
use src\Identity\Domain\Repository\UserRepositoryInterface;
use src\Identity\Domain\Resolver\UuidResolver;
use src\Identity\Domain\Validation\UserValidation;
use src\Identity\Domain\ValueObject\EmailValueObject;

class FindByIdUserUsecase implements FindByIdUserUsecaseInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    ) {}

    public function __invoke(FindByIdUserInputDTO $input): FindByIdUserOutputDTO
    {
        $criteria = array_filter(
            [$input->id, $input->name, $input->email],
            fn (?string $value): bool => $value !== null,
        );

        if (count($criteria) !== 1) {
            throw new InvalidUserSearchException;
        }

        if ($input->id !== null) {
            $id = new UuidResolver($input->id);

            $user = $this->repository->findById($id->value());

        } elseif ($input->email !== null) {
            $email = new EmailValueObject($input->email);

            $user = $this->repository->findByEmail($email->value());

        } else {
            $name = trim($input->name ?? '');

            UserValidation::validateName($name);

            $user = $this->repository->findByName($name);
        }

        if ($user === null) {
            throw new UserNotFoundException;
        }

        return new FindByIdUserOutputDTO(
            id: $user->id()->value(),
            name: $user->name(),
            email: $user->email()->value(),
            role: $user->role()->value,
            isActive: $user->isActive(),
            createdAt: $user->createdAt(),
        );
    }
}
