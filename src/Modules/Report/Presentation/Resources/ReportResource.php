<?php

declare(strict_types=1);

namespace src\Modules\Report\Presentation\Resources;

use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use src\Modules\Report\Application\DTO\ReportDataDTO;

class ReportResource extends JsonResource
{
    public function __construct(private readonly ReportDataDTO $report)
    {
        parent::__construct($report);
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return $this->serializeData($this->report);
    }

    /** @return array<string, mixed> */
    private function serializeData(object $data): array
    {
        $result = [];
        foreach (get_object_vars($data) as $key => $value) {
            $result[Str::snake($key)] = $this->serializeValue($value);
        }
        return $result;
    }

    private function serializeValue(mixed $value): mixed
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format(DateTimeInterface::ATOM);
        }
        if (is_array($value)) {
            return array_map($this->serializeValue(...), $value);
        }
        return is_object($value) ? $this->serializeData($value) : $value;
    }
}
