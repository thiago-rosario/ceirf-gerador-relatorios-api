<?php

declare(strict_types=1);

namespace src\Modules\Identity\Presentation\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use src\Modules\Identity\Domain\Enum\UserRoleEnum;
use Stringable;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $email = $this->input('email');

        if (is_string($email)) {
            $this->merge(['email' => strtolower(trim($email))]);
        }
    }

    /**
     * @return ($key is null ? array{name: string, email: string, password: string, role?: string, coordination_id?: int|null} : mixed)
     */
    public function validated(mixed $key = null, mixed $default = null): mixed
    {
        return parent::validated($key, $default);
    }

    /**
     * @return array<string, list<string|Stringable>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', Rule::unique('users', 'email')],
            'password' => ['required', 'string'],
            'role' => ['sometimes', 'string', Rule::enum(UserRoleEnum::class)],
            'coordination_id' => ['sometimes', 'nullable', 'integer:strict', 'min:1'],
        ];
    }
}
