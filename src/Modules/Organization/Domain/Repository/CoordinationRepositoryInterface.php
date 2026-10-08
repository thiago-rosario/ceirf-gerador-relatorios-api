<?php

declare(strict_types=1);

namespace src\Modules\Organization\Domain\Repository;

use src\Modules\Organization\Domain\Entity\CoordinationEntity;

interface CoordinationRepositoryInterface
{
    /**
     * @return list<CoordinationEntity>
     */
    public function findAll(): array;
}
