<?php

declare(strict_types=1);

namespace App\Http\Request\Identity\User;

use App\Model\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use src\Identity\Domain\Enum\UserRoleEnum;
use Stringable;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        if ($user->can('manage-users')) {
            return true;
        }

        return $user->uuid === $this->route('id')
            && $this->has('password')
            && ! $this->hasAny(['name', 'email', 'role']);
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['id' => $this->route('id')]);

        $email = $this->input('email');

        if (is_string($email)) {
            $this->merge(['email' => strtolower(trim($email))]);
        }
    }

    /**
     * @return ($key is null ? array{id: string, name?: string, email?: string, password?: string, role?: string} : mixed)
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
            'id' => ['required', 'uuid'],
            'name' => ['required_without_all:email,password,role', 'string', 'max:100'],
            'email' => [
                'required_without_all:name,password,role',
                'string',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($this->route('id'), 'uuid'),
            ],
            'password' => ['required_without_all:name,email,role', 'string'],
            'role' => ['required_without_all:name,email,password', 'string', Rule::enum(UserRoleEnum::class)],
        ];
    }
}
