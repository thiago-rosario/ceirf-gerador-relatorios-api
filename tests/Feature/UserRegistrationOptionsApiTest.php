<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Exceptions;
use Mockery\MockInterface;
use src\Modules\Identity\Application\Interfaces\Usecase\User\GetRolesUsecaseInterface;
use src\Modules\Identity\Domain\Enum\UserRoleEnum;
use src\Modules\Identity\Model\Role;
use src\Modules\Identity\Model\User;
use src\Modules\Organization\Application\Interfaces\Usecase\GetCoordinationsUsecaseInterface;

use function Pest\Laravel\mock;

function userRegistrationAccessToken(User $user): string
{
    $response = test()->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertOk();
    Auth::forgetGuards();

    return $response->json('data.access_token');
}

dataset('user registration dropdown endpoints', [
    'roles' => ['/api/roles'],
    'coordinations' => ['/api/coordinations'],
]);

describe('roles listing', function (): void {
    test('returns all available roles ordered by name with values accepted by user registration', function (): void {
        $viewer = Role::query()->create(['code' => 'viewer', 'name' => 'Visualizador']);
        $superuser = Role::query()->create(['code' => 'super_user', 'name' => 'Superusuário']);
        $reviewer = Role::query()->create(['code' => 'reviewer', 'name' => 'Revisor']);
        $operator = Role::query()->create(['code' => 'operator', 'name' => 'Operador']);
        $administrator = User::factory()->superuser()->create();
        $accessToken = userRegistrationAccessToken($administrator);

        $response = $this->withToken($accessToken)->getJson('/api/roles');

        $response->assertOk()->assertExactJson([
            'status' => 'success',
            'data' => [
                'roles' => [
                    ['id' => $operator->id, 'code' => 'operator', 'name' => 'Operador', 'role' => 'OPERATOR'],
                    ['id' => $reviewer->id, 'code' => 'reviewer', 'name' => 'Revisor', 'role' => 'REVIEWER'],
                    ['id' => $superuser->id, 'code' => 'super_user', 'name' => 'Superusuário', 'role' => 'SUPERUSER'],
                    ['id' => $viewer->id, 'code' => 'viewer', 'name' => 'Visualizador', 'role' => 'VIEWER'],
                ],
            ],
        ]);
    });

    test('orders roles with the same name by identifier', function (): void {
        Role::query()->insert([
            ['id' => 40, 'code' => 'viewer', 'name' => 'Perfil'],
            ['id' => 10, 'code' => 'reviewer', 'name' => 'Perfil'],
            ['id' => 30, 'code' => 'super_user', 'name' => 'Perfil'],
            ['id' => 20, 'code' => 'operator', 'name' => 'Perfil'],
        ]);
        $administrator = User::factory()->superuser()->create();
        $accessToken = userRegistrationAccessToken($administrator);

        $response = $this->withToken($accessToken)->getJson('/api/roles');

        $response->assertOk()->assertJsonPath('data.roles.*.id', [10, 20, 30, 40]);
    });

    test('returns an empty successful list when no roles are available', function (): void {
        $administrator = User::factory()->superuser()->make();
        $administrator->setRelation('roles', new Collection([
            new Role(['code' => 'super_user', 'name' => 'Superusuário']),
        ]));

        $response = $this->actingAs($administrator, 'api')->getJson('/api/roles');

        $response->assertOk()->assertExactJson(['status' => 'success', 'data' => ['roles' => []]]);
        $this->assertDatabaseEmpty('roles');
    });
});

describe('coordinations listing', function (): void {
    test('returns every active coordination ordered by name and identifier regardless of role or membership', function (UserRoleEnum $role, ?int $coordinationId): void {
        DB::table('coordinations')->insert([
            ['id' => 40, 'code' => 'ZULU', 'name' => 'Coordenação Z', 'is_active' => true, 'description' => 'Internal description'],
            ['id' => 30, 'code' => 'ALPHA2', 'name' => 'Coordenação A', 'is_active' => true, 'description' => null],
            ['id' => 20, 'code' => 'ALPHA1', 'name' => 'Coordenação A', 'is_active' => true, 'description' => null],
            ['id' => 10, 'code' => 'INACTIVE', 'name' => 'Coordenação 0', 'is_active' => false, 'description' => null],
        ]);
        $user = User::factory()->withRole($role)->create(['coordination_id' => $coordinationId]);
        $accessToken = userRegistrationAccessToken($user);

        $response = $this->withToken($accessToken)->getJson('/api/coordinations');

        $response->assertOk()->assertExactJson([
            'status' => 'success',
            'data' => [
                'coordinations' => [
                    ['id' => 20, 'code' => 'ALPHA1', 'name' => 'Coordenação A'],
                    ['id' => 30, 'code' => 'ALPHA2', 'name' => 'Coordenação A'],
                    ['id' => 40, 'code' => 'ZULU', 'name' => 'Coordenação Z'],
                ],
            ],
        ]);
    })->with([
        'operator with its own coordination' => [UserRoleEnum::OPERATOR, 20],
        'reviewer with its own coordination' => [UserRoleEnum::REVIEWER, 30],
        'viewer with a coordination' => [UserRoleEnum::VIEWER, 40],
        'viewer without a coordination' => [UserRoleEnum::VIEWER, null],
        'superuser without a coordination' => [UserRoleEnum::SUPERUSER, null],
        'legacy operator without a coordination' => [UserRoleEnum::OPERATOR, null],
        'legacy reviewer without a coordination' => [UserRoleEnum::REVIEWER, null],
    ]);

    test('returns an empty successful list when no active coordinations are available', function (): void {
        DB::table('coordinations')->insert([
            'code' => 'INACTIVE', 'name' => 'Coordenação inativa', 'is_active' => false,
        ]);
        $administrator = User::factory()->superuser()->create();
        $accessToken = userRegistrationAccessToken($administrator);

        $response = $this->withToken($accessToken)->getJson('/api/coordinations');

        $response->assertOk()->assertExactJson(['status' => 'success', 'data' => ['coordinations' => []]]);
    });
});

describe('user registration dropdown access', function (): void {
    test('returns 401 when the bearer token is missing', function (string $uri): void {
        $response = $this->getJson($uri);

        $response->assertUnauthorized()->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Não autenticado.');
    })->with('user registration dropdown endpoints');

    test('returns 401 when the bearer token is invalid', function (string $uri): void {
        $response = $this->withToken('invalid-access-token')->getJson($uri);

        $response->assertUnauthorized()->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Não autenticado.');
    })->with('user registration dropdown endpoints');

    test('returns 401 when the bearer token belongs to an inactive user', function (string $uri): void {
        $administrator = User::factory()->superuser()->create();
        $accessToken = userRegistrationAccessToken($administrator);
        $administrator->update(['is_active' => false]);
        Auth::forgetGuards();

        $response = $this->withToken($accessToken)->getJson($uri);

        $response->assertUnauthorized()->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Não autenticado.');
    })->with('user registration dropdown endpoints');

    test('returns 403 when a non-administrator requests roles', function (UserRoleEnum $role): void {
        $user = User::factory()->withRole($role)->create();
        $accessToken = userRegistrationAccessToken($user);

        $response = $this->withToken($accessToken)->getJson('/api/roles');

        $response->assertForbidden()->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Acesso não autorizado.');
    })->with([
        'operator' => [UserRoleEnum::OPERATOR],
        'reviewer' => [UserRoleEnum::REVIEWER],
        'viewer' => [UserRoleEnum::VIEWER],
    ]);

    test('returns 403 when an authenticated user must change the password', function (string $uri, UserRoleEnum $role): void {
        $user = User::factory()->withRole($role)->mustChangePassword()->create();
        $accessToken = userRegistrationAccessToken($user);

        $response = $this->withToken($accessToken)->getJson($uri);

        $response->assertForbidden()->assertJsonPath('status', 'error')
            ->assertJsonPath('data.must_change_password', true)
            ->assertJsonPath('message', 'É necessário alterar a senha antes de continuar.');
    })->with('user registration dropdown endpoints')->with([
        'operator' => [UserRoleEnum::OPERATOR],
        'reviewer' => [UserRoleEnum::REVIEWER],
        'viewer' => [UserRoleEnum::VIEWER],
        'superuser' => [UserRoleEnum::SUPERUSER],
    ]);

    test('returns 500 and reports the original exception when listing unexpectedly fails', function (string $uri, string $usecaseInterface): void {
        $administrator = User::factory()->superuser()->create();
        $accessToken = userRegistrationAccessToken($administrator);
        $exception = new RuntimeException('Dropdown retrieval failed.');
        Exceptions::fake();
        mock($usecaseInterface, function (MockInterface $mock) use ($exception): void {
            $mock->shouldReceive('__invoke')->once()->withNoArgs()->andThrow($exception);
        });

        $response = $this->withToken($accessToken)->getJson($uri);

        $response->assertInternalServerError()->assertExactJson([
            'status' => 'error',
            'data' => [],
            'message' => 'An unexpected error occurred',
            'code' => 500,
        ]);
        Exceptions::assertReported(fn (RuntimeException $reportedException): bool => $reportedException === $exception);
    })->with([
        'roles' => ['/api/roles', GetRolesUsecaseInterface::class],
        'coordinations' => ['/api/coordinations', GetCoordinationsUsecaseInterface::class],
    ]);
});
