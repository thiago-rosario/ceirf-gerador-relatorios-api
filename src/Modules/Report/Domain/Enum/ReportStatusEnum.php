<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Enum;

/**
 * Estados técnicos provisórios de edição e geração; não definem um workflow de aprovação.
 */
enum ReportStatusEnum: string
{
    case DRAFT = 'DRAFT';

    case GENERATED = 'GENERATED';

}
