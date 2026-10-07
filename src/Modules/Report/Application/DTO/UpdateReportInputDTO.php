<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\DTO;

use src\Modules\Report\Domain\ValueObject\ReportAttachmentsValueObject;
use src\Modules\Report\Domain\ValueObject\ReportConclusionValueObject;
use src\Modules\Report\Domain\ValueObject\ReportCoverValueObject;
use src\Modules\Report\Domain\ValueObject\ReportGeneralInformationValueObject;
use src\Modules\Report\Domain\ValueObject\ReportInfrastructureValueObject;
use src\Modules\Report\Domain\ValueObject\ReportLocationValueObject;
use src\Modules\Report\Domain\ValueObject\ReportPhotographicDocumentationValueObject;
use src\Modules\Report\Domain\ValueObject\ReportPreImplementationValueObject;

readonly class UpdateReportInputDTO
{
    public function __construct(
        public string $id,
        public ?ReportCoverValueObject $cover = null,
        public ?ReportGeneralInformationValueObject $generalInformation = null,
        public ?ReportLocationValueObject $location = null,
        public ?ReportInfrastructureValueObject $infrastructure = null,
        public ?ReportPreImplementationValueObject $preImplementation = null,
        public ?ReportPhotographicDocumentationValueObject $photographicDocumentation = null,
        public ?ReportAttachmentsValueObject $attachments = null,
        public ?ReportConclusionValueObject $conclusion = null,
    ) {}
}
