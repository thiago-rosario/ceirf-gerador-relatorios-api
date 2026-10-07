<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Validation;

use src\Modules\Report\Domain\Exception\IncompleteReportException;
use src\Modules\Report\Domain\Exception\InvalidMunicipalityException;
use src\Modules\Report\Domain\Exception\InvalidReportTypologyException;
use src\Modules\Report\Domain\Exception\InvalidSeiNumberException;
use src\Modules\Report\Domain\ValueObject\MunicipalityValueObject;
use src\Modules\Report\Domain\ValueObject\ReportCoverValueObject;
use src\Modules\Report\Domain\ValueObject\SeiNumberValueObject;

final class ReportCoverValidation
{
    public static function validate(ReportCoverValueObject $cover): void
    {
        if (mb_strlen($cover->typology()) > 100) {
            throw new InvalidReportTypologyException;
        }
    }

    public static function validateMunicipality(MunicipalityValueObject $municipality): void
    {
        if ($municipality->id() <= 0
            || ReportTextValidation::isBlank($municipality->name())
            || mb_strlen($municipality->name()) > 100
            || $municipality->stateCode() !== 'BA') {
            throw new InvalidMunicipalityException;
        }
    }

    public static function validateSeiNumber(SeiNumberValueObject $seiNumber): void
    {
        if (ReportTextValidation::isBlank($seiNumber->value()) || mb_strlen($seiNumber->value()) > 80) {
            throw new InvalidSeiNumberException;
        }
    }

    public static function validateForGeneration(ReportCoverValueObject $cover): void
    {
        self::validate($cover);

        if ($cover->municipality() === null
            || $cover->force() === null
            || $cover->size() === null
            || ReportTextValidation::isBlank($cover->typology())
            || $cover->seiNumber() === null) {
            throw new IncompleteReportException('A capa deve possuir município, força, tamanho, tipologia e número SEI para gerar o relatório.');
        }
    }
}
