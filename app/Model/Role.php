<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use src\Identity\Domain\Enum\UserRoleEnum;
use UnexpectedValueException;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 */
class Role extends Model
{
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = ['code', 'name'];

    public static function forRole(UserRoleEnum $role): self
    {
        [$code, $name] = match ($role) {
            UserRoleEnum::OPERATOR => ['operator', 'Operador'],
            UserRoleEnum::VIEWER => ['viewer', 'Visualizador'],
            UserRoleEnum::REVIEWER => ['reviewer', 'Revisor'],
            UserRoleEnum::SUPERUSER => ['super_user', 'Superusuário'],
        };

        return static::query()->firstOrCreate(['code' => $code], ['name' => $name]);
    }

    public function toUserRole(): UserRoleEnum
    {
        return match ($this->code) {
            'operator' => UserRoleEnum::OPERATOR,
            'viewer' => UserRoleEnum::VIEWER,
            'reviewer' => UserRoleEnum::REVIEWER,
            'super_user' => UserRoleEnum::SUPERUSER,
            default => throw new UnexpectedValueException('Perfil de usuário inválido: '.$this->code),
        };
    }
}
