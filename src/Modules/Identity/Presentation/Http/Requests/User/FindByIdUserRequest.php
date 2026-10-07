<?php

declare(strict_types=1);

namespace src\Modules\Identity\Presentation\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class FindByIdUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->route('id') !== null) {
            $this->merge(['id' => $this->route('id')]);
        }

        $email = $this->input('email');

        if (is_string($email)) {
            $this->merge(['email' => strtolower(trim($email))]);
        }
    }

    /**
     * @return ($key is null ? array{id?: string, name?: string, email?: string} : mixed)
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
            'id' => ['required_without_all:name,email', 'uuid', 'prohibits:name,email'],
            'name' => ['required_without_all:id,email', 'string', 'max:100', 'prohibits:id,email'],
            'email' => ['required_without_all:id,name', 'string', 'email', 'max:150', 'prohibits:id,name'],
        ];
    }
}
