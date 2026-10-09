<?php

declare(strict_types=1);

namespace src\Modules\Identity\Presentation\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use src\Modules\Identity\Domain\Enum\UserRoleEnum;
use src\Modules\Identity\Model\User;
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
            && ! $this->hasAny(['name', 'email', 'role', 'coordination_id']);
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
     * @return ($key is null ? array{id: string, name?: string, email?: string, password?: string, role?: string, coordination_id?: int|null} : mixed)
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
        $coordinationProvided = $this->has('coordination_id');

        return [
            'id' => ['required', 'uuid'],
            'name' => [$coordinationProvided ? 'sometimes' : 'required_without_all:email,password,role', 'string', 'max:100'],
            'email' => [
                $coordinationProvided ? 'sometimes' : 'required_without_all:name,password,role',
                'string',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($this->route('id'), 'uuid'),
            ],
            'password' => [$coordinationProvided ? 'sometimes' : 'required_without_all:name,email,role', 'string'],
            'role' => [$coordinationProvided ? 'sometimes' : 'required_without_all:name,email,password', 'string', Rule::enum(UserRoleEnum::class)],
            'coordination_id' => ['sometimes', 'nullable', 'integer:strict', 'min:1'],
        ];
    }
}
