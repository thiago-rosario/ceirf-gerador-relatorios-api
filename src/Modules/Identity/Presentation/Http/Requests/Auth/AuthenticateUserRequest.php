<?php

declare(strict_types=1);

namespace src\Modules\Identity\Presentation\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class AuthenticateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return ($key is null ? array{email: string, password: string} : mixed)
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
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }
}
