<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Enum;

enum ChecklistAnswerEnum: string
{
    case YES = 'SIM';

    case NO = 'NÃO';

    case NOT_APPLICABLE = 'NÃO SE APLICA';

}
