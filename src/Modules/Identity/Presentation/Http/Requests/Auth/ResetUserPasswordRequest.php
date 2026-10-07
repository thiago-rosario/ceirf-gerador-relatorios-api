<?php

declare(strict_types=1);

namespace src\Modules\Identity\Presentation\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResetUserPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-users') ?? false;
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
            'id' => ['required', 'string', 'uuid'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['id' => $this->route('id')]);
    }
}
