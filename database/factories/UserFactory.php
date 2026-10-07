<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use src\Modules\Identity\Domain\Enum\UserRoleEnum;
use src\Modules\Identity\Model\Role;
use src\Modules\Identity\Model\User;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'is_active' => true,
            'must_change_password' => false,
        ];
    }

    public function configure(): static
    {
        return $this->withRole(UserRoleEnum::OPERATOR);
    }

    public function withRole(UserRoleEnum $role): static
    {
        return $this->afterCreating(function (User $user) use ($role): void {
            $user->roles()->sync([Role::forRole($role)->id]);
            $user->unsetRelation('roles');
        });
    }

    public function superuser(): static
    {
        return $this->withRole(UserRoleEnum::SUPERUSER);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => ['is_active' => false]);
    }

    public function mustChangePassword(): static
    {
        return $this->state(fn (array $attributes): array => ['must_change_password' => true]);
    }
}
