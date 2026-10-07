<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Enum;

/**
 * Decisão individual de revisão humana, sem determinar quantidade de aprovações.
 */
enum ReportReviewDecisionEnum: string
{
    case APPROVED = 'APPROVED';

    case REJECTED = 'REJECTED';

}
