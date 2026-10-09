<?php

declare(strict_types=1);

namespace src\Modules\Organization\Application\Usecase;

use src\Modules\Organization\Application\DTO\CoordinationDataDTO;
use src\Modules\Organization\Application\DTO\GetCoordinationsOutputDTO;
use src\Modules\Organization\Application\Interfaces\Usecase\GetCoordinationsUsecaseInterface;
use src\Modules\Organization\Domain\Repository\CoordinationRepositoryInterface;

class GetCoordinationsUsecase implements GetCoordinationsUsecaseInterface
{
    public function __construct(
        private readonly CoordinationRepositoryInterface $repository,
    ) {}

    public function __invoke(): GetCoordinationsOutputDTO
    {
        $coordinations = $this->repository->findAll();

        $coordinationsData = [];

        foreach ($coordinations as $coordination) {
            $coordinationsData[] = new CoordinationDataDTO(
                id: $coordination->id(),
                code: $coordination->code(),
                name: $coordination->name(),
            );
        }

        return new GetCoordinationsOutputDTO(coordinations: $coordinationsData);
    }
}
