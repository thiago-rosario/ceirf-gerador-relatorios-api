<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Enum;

enum ReportSizeEnum: string
{
    case ONE_B = '1B';

    case ONE_A = '1A';

    case ONE = '1';

    case ONE_B_ONE_B = '1B-1B';

    case ONE_B_ONE_A = '1B-1A';

    case ONE_B_ONE = '1B-1';

    case ONE_A_ONE_A = '1A-1A';

    case ONE_A_A = '1A-A';

    case ONE_ONE = '1-1';

    case ONE_A_ONE_B = '1A-1B';

    case WITHOUT_STANDARD = 'SEM PADRÃO';

}
