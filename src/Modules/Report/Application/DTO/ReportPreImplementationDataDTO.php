<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\DTO;

readonly class ReportPreImplementationDataDTO
{
    public function __construct(public ?ReportImageDataDTO $image) {}
}
