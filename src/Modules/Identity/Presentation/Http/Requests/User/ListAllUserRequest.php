<?php

declare(strict_types=1);

namespace src\Modules\Identity\Presentation\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class ListAllUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $filter = $this->input('filter');
        $orderBy = $this->input('order_by');

        if ($filter === null) {
            $this->merge(['filter' => '']);
        }

        if (is_string($orderBy)) {
            $this->merge(['order_by' => strtoupper($orderBy)]);
        }
    }

    /**
     * @return ($key is null ? array{filter?: string, order_by?: string} : mixed)
     */
    public function validated(mixed $key = null, mixed $default = null): mixed
    {
        return parent::validated($key, $default);
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'filter' => ['sometimes', 'string', 'max:255'],
            'order_by' => ['sometimes', 'string', 'in:ASC,DESC'],
        ];
    }
}
