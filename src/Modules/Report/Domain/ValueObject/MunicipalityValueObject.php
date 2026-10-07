<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\ValueObject;

use src\Modules\Report\Domain\Validation\ReportCoverValidation;

/**
 * Referência a um município proveniente do catálogo oficial da aplicação.
 * A resolução do identificador no catálogo pertence à camada de aplicação.
 */
readonly class MunicipalityValueObject
{
    private string $stateCode;

    public function __construct(
        private int $id,
        private string $name,
        string $stateCode = 'BA',
    ) {
        $this->stateCode = strtoupper(trim($stateCode));

        ReportCoverValidation::validateMunicipality($this);
    }

    public function id(): int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function stateCode(): string
    {
        return $this->stateCode;
    }
}
