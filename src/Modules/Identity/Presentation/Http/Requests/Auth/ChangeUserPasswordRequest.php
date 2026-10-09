<?php

declare(strict_types=1);

namespace src\Modules\Identity\Presentation\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use src\Modules\Identity\Model\User;

class ChangeUserPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof User;
    }

    /**
     * @return ($key is null ? array{current_password: string, password: string, password_confirmation: string} : mixed)
     */
    public function validated(mixed $key = null, mixed $default = null): mixed
    {
        return parent::validated($key, $default);
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
            'password_confirmation' => ['required', 'string'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'current_password.required' => 'Informe a senha atual.',
            'current_password.string' => 'A senha atual deve ser um texto.',
            'password.required' => 'Informe a nova senha.',
            'password.string' => 'A nova senha deve ser um texto.',
            'password.min' => 'A nova senha deve ter pelo menos 8 caracteres.',
            'password.confirmed' => 'A confirmação da nova senha não confere.',
            'password.different' => 'A nova senha deve ser diferente da senha atual.',
            'password_confirmation.required' => 'Confirme a nova senha.',
            'password_confirmation.string' => 'A confirmação da nova senha deve ser um texto.',
        ];
    }
}
