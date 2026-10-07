<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Enum;

enum ReportAttachmentTypeEnum: string
{
    case MUNICIPALITY_LOCATION_MAP = 'MUNICIPALITY_LOCATION_MAP';

    case TOPOGRAPHIC_PLAN = 'TOPOGRAPHIC_PLAN';

    case OTHER = 'OTHER';

}
