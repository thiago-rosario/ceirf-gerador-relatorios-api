<?php

declare(strict_types=1);

namespace App\Http\Request\Identity\User;

use Illuminate\Foundation\Http\FormRequest;

class DeactivateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['id' => $this->route('id')]);
    }

    /**
     * @return ($key is null ? array{id: string} : mixed)
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
            'id' => ['required', 'uuid'],
        ];
    }
}
