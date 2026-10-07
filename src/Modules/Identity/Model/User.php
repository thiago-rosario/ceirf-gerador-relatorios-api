<?php

declare(strict_types=1);

namespace src\Modules\Identity\Model;

use Carbon\CarbonImmutable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use src\Modules\Identity\Domain\Enum\UserRoleEnum;

/**
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $email
 * @property string $password
 * @property bool $is_active
 * @property bool $must_change_password
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read UserRoleEnum $role
 * @property-read Collection<int, Role> $roles
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasUuids;

    /** @var list<string> */
    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
        'is_active',
        'must_change_password',
        'created_at',
        'updated_at',
    ];

    /** @var list<string> */
    protected $hidden = ['password'];

    /** @var array<string, mixed> */
    protected $attributes = [
        'is_active' => true,
        'must_change_password' => false,
    ];

    /** @return list<string> */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /** @return BelongsToMany<Role, $this> */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    /** @return Attribute<UserRoleEnum, never> */
    protected function role(): Attribute
    {
        return Attribute::get($this->primaryRole(...))->withoutObjectCaching();
    }

    private function primaryRole(): UserRoleEnum
    {
        foreach ([UserRoleEnum::SUPERUSER, UserRoleEnum::REVIEWER, UserRoleEnum::OPERATOR, UserRoleEnum::VIEWER] as $role) {
            if ($this->roles->contains(fn (Role $model): bool => $model->toUserRole() === $role)) {
                return $role;
            }
        }

        return UserRoleEnum::OPERATOR;
    }

    /** @return Attribute<string, string> */
    protected function email(): Attribute
    {
        return Attribute::make(set: fn (string $value): string => mb_strtolower(trim($value)));
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
