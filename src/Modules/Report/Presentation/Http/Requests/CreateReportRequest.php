<?php

declare(strict_types=1);

namespace src\Modules\Report\Presentation\Http\Requests;

use Stringable;

/** @phpstan-import-type ReportPayload from ReportPayloadRequest */
class CreateReportRequest extends ReportPayloadRequest
{
    /** @return ($key is null ? ReportPayload : mixed) */
    public function validated(mixed $key = null, mixed $default = null): mixed
    {
        return parent::validated($key, $default);
    }

    /** @return array<string, list<string|Stringable>> */
    public function rules(): array
    {
        $rules = $this->sectionRules();
        foreach (['location.location_map', 'location.municipality_in_state_map', 'pre_implementation.image', 'photographic_documentation.images', 'attachments.municipality_location_map', 'attachments.topographic_plan', 'attachments.others'] as $path) {
            $rules[$path] = ['prohibited'];
        }
        return $rules;
    }
}
