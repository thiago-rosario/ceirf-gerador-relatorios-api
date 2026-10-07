<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Validation;

use src\Modules\Report\Domain\Exception\IncompleteReportException;
use src\Modules\Report\Domain\ValueObject\ReportChecklistValueObject;
use src\Modules\Report\Domain\ValueObject\ReportInfrastructureValueObject;

final class ReportChecklistValidation
{
    public static function validateForGeneration(
        ReportInfrastructureValueObject|ReportChecklistValueObject $checklist,
    ): void {
        foreach ($checklist->answers() as $question => $answer) {
            if ($answer === null) {
                $section = $checklist instanceof ReportInfrastructureValueObject
                    ? 'infraestrutura existente'
                    : 'checklist do terreno';

                throw new IncompleteReportException(
                    sprintf('O item %s da seção %s deve estar respondido para gerar o relatório.', $question, $section),
                );
            }
        }
    }
}
