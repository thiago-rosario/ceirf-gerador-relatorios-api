<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\Usecase\User;

use src\Modules\Identity\Application\DTO\User\FindByIdUserInputDTO;
use src\Modules\Identity\Application\DTO\User\FindByIdUserOutputDTO;
use src\Modules\Identity\Application\Exception\InvalidUserSearchException;
use src\Modules\Identity\Application\Exception\UserNotFoundException;
use src\Modules\Identity\Application\Interfaces\Usecase\User\FindByIdUserUsecaseInterface;
use src\Modules\Identity\Domain\Repository\UserRepositoryInterface;
use src\Modules\Identity\Domain\Validation\UserValidation;
use src\Modules\Identity\Domain\ValueObject\EmailValueObject;
use src\Modules\Organization\Application\DTO\CoordinationDataDTO;
use src\Modules\Organization\Domain\Repository\CoordinationRepositoryInterface;
use src\Modules\Shared\Resolver\UuidResolver;

class FindByIdUserUsecase implements FindByIdUserUsecaseInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
        private readonly CoordinationRepositoryInterface $coordinationRepository,
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

        $coordination = $user->coordinationId() === null ? null : $this->coordinationRepository->findById($user->coordinationId());

        return new FindByIdUserOutputDTO(
            id: $user->id()->value(),
            name: $user->name(),
            email: $user->email()->value(),
            role: $user->role()->value,
            isActive: $user->isActive(),
            createdAt: $user->createdAt(),
            mustChangePassword: $user->mustChangePassword(),
            coordinationId: $user->coordinationId(),
            coordination: $coordination === null ? null : new CoordinationDataDTO(
                id: $coordination->id(),
                code: $coordination->code(),
                name: $coordination->name(),
            ),
        );
    }
}
