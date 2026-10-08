<?php

declare(strict_types=1);

namespace src\Modules\Report\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListReportsRequest extends FormRequest
{
    /** @return ($key is null ? array{user_id?: string, coordination_id?: int|string, municipality_id?: int|string} : mixed) */
    public function validated(mixed $key = null, mixed $default = null): mixed
    {
        return parent::validated($key, $default);
    }

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'uuid', 'prohibits:municipality_id'],
            'coordination_id' => ['sometimes', 'integer', 'min:1', 'prohibits:municipality_id'],
            'municipality_id' => ['sometimes', 'integer', 'min:1', 'prohibits:user_id,coordination_id'],
        ];
    }
}
