<?php

declare(strict_types=1);

namespace src\Modules\Report\Presentation\Http\Requests;

use src\Modules\Identity\Domain\Enum\UserRoleEnum;
use src\Modules\Identity\Model\User;

class WriteReportIdRequest extends ReportIdRequest
{
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = $this->user();
        return $user !== null && $user->role !== UserRoleEnum::VIEWER;
    }
}
