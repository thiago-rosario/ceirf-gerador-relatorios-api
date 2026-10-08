<?php

declare(strict_types=1);

namespace src\Modules\Organization\Application\DTO\Coordination;

readonly class GetCoordinationsOutputDTO
{
    /**
     * @param  list<CoordinationDataDTO>  $coordinations
     */
    public function __construct(
        public array $coordinations,
    ) {}
}
