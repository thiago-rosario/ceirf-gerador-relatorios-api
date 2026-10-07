<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Enum;

enum ForceEnum: string
{
    case PM = 'PM';

    case PC = 'PC';

    case CBM = 'CBM';

    case DPT = 'DPT';

    case PC_PM = 'PC-PM';

    case SSP = 'SSP';

}
