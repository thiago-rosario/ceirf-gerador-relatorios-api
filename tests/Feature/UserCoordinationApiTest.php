<?php

declare(strict_types=1);

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use src\Modules\Identity\Domain\Enum\UserRoleEnum;
use src\Modules\Identity\Model\User;

function userCoordinationAccessToken(User $user): string
{
    $response = test()->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertOk();
    Auth::forgetGuards();

    return $response->json('data.access_token');
}

/**
 * @return array{id: int, code: string, name: string}
 */
function userCoordinationRecord(bool $isActive = true): array
{
    $attributes = [
        'code' => fake()->unique()->bothify('COORD-#####'),
        'name' => fake()->company(),
    ];
    $id = DB::table('coordinations')->insertGetId([...$attributes, 'is_active' => $isActive]);

    return ['id' => $id, ...$attributes];
}

describe('user coordination creation', function (): void {
    test('a superuser creates a user with exactly one active coordination', function (UserRoleEnum $role): void {
        $coordination = userCoordinationRecord();
        $administrator = User::factory()->superuser()->create();
        $accessToken = userCoordinationAccessToken($administrator);

        $response = $this->withToken($accessToken)->postJson('/api/users', [
            'name' => 'Ana Silva', 'email' => 'ana@example.com', 'password' => 'new-password',
            'role' => $role->value, 'coordination_id' => $coordination['id'],
        ]);

        $response->assertCreated()->assertJsonPath('data.role', $role->value)
            ->assertJsonPath('data.coordination_id', $coordination['id'])
            ->assertJsonPath('data.coordination', $coordination);
        $this->assertDatabaseHas('users', ['email' => 'ana@example.com', 'coordination_id' => $coordination['id']]);
        $this->assertDatabaseCount('users', 2);
    })->with([
        'operator' => [UserRoleEnum::OPERATOR],
        'reviewer' => [UserRoleEnum::REVIEWER],
        'viewer' => [UserRoleEnum::VIEWER],
    ]);

    test('operators and reviewers require a coordination including the default role', function (?UserRoleEnum $role, bool $explicitNull): void {
        $administrator = User::factory()->superuser()->create();
        $accessToken = userCoordinationAccessToken($administrator);
        $attributes = ['name' => 'Ana Silva', 'email' => 'ana@example.com', 'password' => 'new-password'];
        if ($role !== null) {
            $attributes['role'] = $role->value;
        }
        if ($explicitNull) {
            $attributes['coordination_id'] = null;
        }

        $response = $this->withToken($accessToken)->postJson('/api/users', $attributes);

        $response->assertUnprocessable()->assertJsonValidationErrors('coordination_id');
        $this->assertDatabaseCount('users', 1);
    })->with([
        'default operator omitted' => [null, false],
        'operator omitted' => [UserRoleEnum::OPERATOR, false],
        'operator null' => [UserRoleEnum::OPERATOR, true],
        'reviewer omitted' => [UserRoleEnum::REVIEWER, false],
        'reviewer null' => [UserRoleEnum::REVIEWER, true],
    ]);

    test('viewers and superusers can be created without a coordination', function (UserRoleEnum $role, bool $explicitNull): void {
        $administrator = User::factory()->superuser()->create();
        $accessToken = userCoordinationAccessToken($administrator);
        $attributes = [
            'name' => 'Ana Silva', 'email' => 'ana@example.com', 'password' => 'new-password', 'role' => $role->value,
        ];
        if ($explicitNull) {
            $attributes['coordination_id'] = null;
        }

        $response = $this->withToken($accessToken)->postJson('/api/users', $attributes);

        $response->assertCreated()->assertJsonPath('data.coordination_id', null)->assertJsonPath('data.coordination', null);
        $this->assertDatabaseHas('users', ['email' => 'ana@example.com', 'coordination_id' => null]);
    })->with([
        'viewer omitted' => [UserRoleEnum::VIEWER, false],
        'viewer null' => [UserRoleEnum::VIEWER, true],
        'superuser omitted' => [UserRoleEnum::SUPERUSER, false],
        'superuser null' => [UserRoleEnum::SUPERUSER, true],
    ]);

    test('superusers cannot be assigned a coordination during creation', function (): void {
        $coordination = userCoordinationRecord();
        $administrator = User::factory()->superuser()->create();
        $accessToken = userCoordinationAccessToken($administrator);

        $response = $this->withToken($accessToken)->postJson('/api/users', [
            'name' => 'Ana Silva', 'email' => 'ana@example.com', 'password' => 'new-password',
            'role' => UserRoleEnum::SUPERUSER->value, 'coordination_id' => $coordination['id'],
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('coordination_id');
        $this->assertDatabaseCount('users', 1);
    });
});

describe('user coordination queries', function (): void {
    test('user lookup and exact searches return the persisted coordination even when inactive', function (string $criterion): void {
        $coordination = userCoordinationRecord(false);
        $administrator = User::factory()->superuser()->create();
        $user = User::factory()->create(['name' => 'Ana Silva', 'email' => 'ana@example.com', 'coordination_id' => $coordination['id']]);
        $accessToken = userCoordinationAccessToken($administrator);
        $uri = $criterion === 'route'
            ? '/api/users/'.$user->uuid
            : '/api/users/search?'.http_build_query([$criterion => $criterion === 'id' ? $user->uuid : $user->{$criterion}]);

        $response = $this->withToken($accessToken)->getJson($uri);

        $response->assertOk()->assertJsonPath('data.id', $user->uuid)
            ->assertJsonPath('data.coordination_id', $coordination['id'])
            ->assertJsonPath('data.coordination', $coordination)
            ->assertJsonMissingPath('data.password');
    })->with(['UUID route' => ['route'], 'UUID search' => ['id'], 'name search' => ['name'], 'email search' => ['email']]);

    test('the user list returns linked and unlinked coordination summaries', function (): void {
        $coordination = userCoordinationRecord();
        $administrator = User::factory()->superuser()->create(['name' => 'Administrator']);
        $linkedUser = User::factory()->create(['name' => 'Ana Silva', 'coordination_id' => $coordination['id']]);
        $unlinkedUser = User::factory()->withRole(UserRoleEnum::VIEWER)->create(['name' => 'Ana Souza']);
        $accessToken = userCoordinationAccessToken($administrator);

        $response = $this->withToken($accessToken)->getJson('/api/users?filter=Ana&order_by=ASC');

        $response->assertOk()->assertJsonCount(2, 'data.users')
            ->assertJsonPath('data.users.0.id', $linkedUser->uuid)
            ->assertJsonPath('data.users.0.coordination_id', $coordination['id'])
            ->assertJsonPath('data.users.0.coordination', $coordination)
            ->assertJsonPath('data.users.1.id', $unlinkedUser->uuid)
            ->assertJsonPath('data.users.1.coordination_id', null)
            ->assertJsonPath('data.users.1.coordination', null);
    });
});

describe('user coordination updates', function (): void {
    test('a superuser replaces only the coordination while preserving the other user fields', function (): void {
        $originalCoordination = userCoordinationRecord();
        $newCoordination = userCoordinationRecord();
        $administrator = User::factory()->superuser()->create();
        $user = User::factory()->withRole(UserRoleEnum::REVIEWER)->create(['coordination_id' => $originalCoordination['id']]);
        $originalAttributes = $user->only(['name', 'email', 'password', 'is_active', 'must_change_password']);
        $accessToken = userCoordinationAccessToken($administrator);

        $response = $this->withToken($accessToken)->patchJson('/api/users/'.$user->uuid, ['coordination_id' => $newCoordination['id']]);

        $response->assertOk()->assertJsonPath('data.coordination_id', $newCoordination['id'])
            ->assertJsonPath('data.coordination', $newCoordination);
        $this->assertDatabaseHas('users', ['id' => $user->id, ...$originalAttributes, 'coordination_id' => $newCoordination['id']]);
        expect($user->refresh()->role)->toBe(UserRoleEnum::REVIEWER);
    });

    test('an explicit null removes a viewer coordination', function (): void {
        $coordination = userCoordinationRecord();
        $administrator = User::factory()->superuser()->create();
        $user = User::factory()->withRole(UserRoleEnum::VIEWER)->create(['coordination_id' => $coordination['id']]);
        $accessToken = userCoordinationAccessToken($administrator);

        $response = $this->withToken($accessToken)->patchJson('/api/users/'.$user->uuid, ['coordination_id' => null]);

        $response->assertOk()->assertJsonPath('data.coordination_id', null)->assertJsonPath('data.coordination', null);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'coordination_id' => null]);
    });

    test('operators and reviewers cannot remove their coordination and no other edits are persisted', function (UserRoleEnum $role): void {
        $coordination = userCoordinationRecord();
        $administrator = User::factory()->superuser()->create();
        $user = User::factory()->withRole($role)->create(['name' => 'Original User', 'coordination_id' => $coordination['id']]);
        $originalPassword = $user->password;
        $accessToken = userCoordinationAccessToken($administrator);

        $response = $this->withToken($accessToken)->patchJson('/api/users/'.$user->uuid, [
            'name' => 'Changed User', 'password' => 'changed-password', 'coordination_id' => null,
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('coordination_id');
        $this->assertDatabaseHas('users', [
            'id' => $user->id, 'name' => 'Original User', 'password' => $originalPassword, 'coordination_id' => $coordination['id'],
        ]);
    })->with(['operator' => [UserRoleEnum::OPERATOR], 'reviewer' => [UserRoleEnum::REVIEWER]]);

    test('changing a viewer to a writer requires a coordination in the final user state', function (UserRoleEnum $role): void {
        $coordination = userCoordinationRecord();
        $administrator = User::factory()->superuser()->create();
        $user = User::factory()->withRole(UserRoleEnum::VIEWER)->create();
        $accessToken = userCoordinationAccessToken($administrator);

        $this->withToken($accessToken)->patchJson('/api/users/'.$user->uuid, ['role' => $role->value])
            ->assertUnprocessable()->assertJsonValidationErrors('coordination_id');
        expect($user->refresh()->role)->toBe(UserRoleEnum::VIEWER);

        $response = $this->withToken($accessToken)->patchJson('/api/users/'.$user->uuid, [
            'role' => $role->value, 'coordination_id' => $coordination['id'],
        ]);

        $response->assertOk()->assertJsonPath('data.role', $role->value)->assertJsonPath('data.coordination', $coordination);
        expect($user->refresh()->role)->toBe($role);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'coordination_id' => $coordination['id']]);
    })->with(['operator' => [UserRoleEnum::OPERATOR], 'reviewer' => [UserRoleEnum::REVIEWER]]);

    test('changing to superuser clears the link when coordination is omitted', function (): void {
        $coordination = userCoordinationRecord();
        $administrator = User::factory()->superuser()->create();
        $user = User::factory()->create(['coordination_id' => $coordination['id']]);
        $accessToken = userCoordinationAccessToken($administrator);

        $response = $this->withToken($accessToken)->patchJson('/api/users/'.$user->uuid, ['role' => UserRoleEnum::SUPERUSER->value]);

        $response->assertOk()->assertJsonPath('data.role', UserRoleEnum::SUPERUSER->value)
            ->assertJsonPath('data.coordination_id', null)->assertJsonPath('data.coordination', null);
        expect($user->refresh()->role)->toBe(UserRoleEnum::SUPERUSER);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'coordination_id' => null]);
    });

    test('superusers cannot receive an explicit coordination including during a role transition', function (bool $isAlreadySuperuser): void {
        $coordination = userCoordinationRecord();
        $administrator = User::factory()->superuser()->create();
        $originalRole = $isAlreadySuperuser ? UserRoleEnum::SUPERUSER : UserRoleEnum::OPERATOR;
        $originalCoordinationId = $isAlreadySuperuser ? null : $coordination['id'];
        $user = User::factory()->withRole($originalRole)->create(['coordination_id' => $originalCoordinationId]);
        $accessToken = userCoordinationAccessToken($administrator);

        $response = $this->withToken($accessToken)->patchJson('/api/users/'.$user->uuid, [
            'role' => UserRoleEnum::SUPERUSER->value, 'coordination_id' => $coordination['id'],
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('coordination_id');
        expect($user->refresh()->role)->toBe($originalRole);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'coordination_id' => $originalCoordinationId]);
    })->with(['existing superuser' => [true], 'promoted operator' => [false]]);

    test('changing to viewer permits removing the link in the same update', function (): void {
        $coordination = userCoordinationRecord();
        $administrator = User::factory()->superuser()->create();
        $user = User::factory()->create(['coordination_id' => $coordination['id']]);
        $accessToken = userCoordinationAccessToken($administrator);

        $response = $this->withToken($accessToken)->patchJson('/api/users/'.$user->uuid, [
            'role' => UserRoleEnum::VIEWER->value, 'coordination_id' => null,
        ]);

        $response->assertOk()->assertJsonPath('data.role', UserRoleEnum::VIEWER->value)->assertJsonPath('data.coordination', null);
        expect($user->refresh()->role)->toBe(UserRoleEnum::VIEWER);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'coordination_id' => null]);
    });

    test('omission and resending the same identifier preserve the current inactive coordination during unrelated edits', function (bool $explicitCoordination): void {
        $coordination = userCoordinationRecord(false);
        $administrator = User::factory()->superuser()->create();
        $user = User::factory()->create(['coordination_id' => $coordination['id']]);
        $accessToken = userCoordinationAccessToken($administrator);
        $attributes = ['name' => 'Updated User'];
        if ($explicitCoordination) {
            $attributes['coordination_id'] = $coordination['id'];
        }

        $response = $this->withToken($accessToken)->patchJson('/api/users/'.$user->uuid, $attributes);

        $response->assertOk()->assertJsonPath('data.coordination', $coordination);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated User', 'coordination_id' => $coordination['id']]);
        $this->withToken($accessToken)->getJson('/api/coordinations')->assertOk()->assertJsonCount(0, 'data.coordinations');
    })->with(['omitted' => [false], 'same identifier' => [true]]);
});

describe('invalid coordination assignments', function (): void {
    test('creation and updates reject a missing or inactive coordination without saving changes', function (string $method, bool $inactive): void {
        $originalCoordination = userCoordinationRecord();
        $invalidCoordinationId = $inactive ? userCoordinationRecord(false)['id'] : 99999;
        $administrator = User::factory()->superuser()->create();
        $user = User::factory()->create(['name' => 'Original User', 'coordination_id' => $originalCoordination['id']]);
        $accessToken = userCoordinationAccessToken($administrator);
        $uri = $method === 'POST' ? '/api/users' : '/api/users/'.$user->uuid;

        $response = $this->withToken($accessToken)->json($method, $uri, [
            'name' => 'Changed User', 'email' => 'changed@example.com', 'password' => 'changed-password',
            'role' => UserRoleEnum::VIEWER->value, 'coordination_id' => $invalidCoordinationId,
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('coordination_id');
        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseMissing('users', ['email' => 'changed@example.com']);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Original User', 'coordination_id' => $originalCoordination['id']]);
        expect($user->refresh()->role)->toBe(UserRoleEnum::OPERATOR);
        expect(Hash::check('password', $user->password))->toBeTrue();
    })->with([
        'create missing' => ['POST', false], 'create inactive' => ['POST', true],
        'update missing' => ['PATCH', false], 'update inactive' => ['PATCH', true],
    ]);

    test('coordination identifiers reject invalid values on creation and update', function (mixed $invalidId, string $method): void {
        $coordination = userCoordinationRecord();
        $administrator = User::factory()->superuser()->create();
        $user = User::factory()->create(['coordination_id' => $coordination['id']]);
        $accessToken = userCoordinationAccessToken($administrator);
        $uri = $method === 'POST' ? '/api/users' : '/api/users/'.$user->uuid;

        $response = $this->withToken($accessToken)->json($method, $uri, [
            'name' => 'Changed User', 'email' => 'changed@example.com', 'password' => 'changed-password',
            'role' => UserRoleEnum::OPERATOR->value, 'coordination_id' => $invalidId,
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('coordination_id');
        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseMissing('users', ['email' => 'changed@example.com']);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'coordination_id' => $coordination['id']]);
    })->with([
        'zero' => [0], 'negative' => [-1], 'fraction' => [1.5], 'text' => ['invalid-id'],
        'numeric string' => ['1'], 'array' => [[]], 'boolean' => [true],
    ])->with(['creation' => ['POST'], 'update' => ['PATCH']]);

    test('a persistence failure rolls back coordination, role, password and session changes together', function (): void {
        $originalCoordination = userCoordinationRecord();
        $newCoordination = userCoordinationRecord();
        $administrator = User::factory()->superuser()->create();
        $user = User::factory()->create(['coordination_id' => $originalCoordination['id']]);
        $originalPassword = $user->password;
        $userAccessToken = userCoordinationAccessToken($user);
        $accessToken = userCoordinationAccessToken($administrator);
        $exception = new RuntimeException('User coordination persistence failed.');
        Exceptions::fake();
        User::updated(function (User $updatedUser) use ($user, $exception): void {
            if ($updatedUser->id === $user->id) {
                throw $exception;
            }
        });

        $response = $this->withToken($accessToken)->patchJson('/api/users/'.$user->uuid, [
            'password' => 'changed-password', 'role' => UserRoleEnum::REVIEWER->value, 'coordination_id' => $newCoordination['id'],
        ]);

        $response->assertInternalServerError();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'password' => $originalPassword, 'coordination_id' => $originalCoordination['id']]);
        $this->assertDatabaseHas('user_access_tokens', ['token' => hash('sha256', $userAccessToken)]);
        expect($user->refresh()->role)->toBe(UserRoleEnum::OPERATOR);
        Exceptions::assertReported(fn (RuntimeException $reportedException): bool => $reportedException === $exception);
    });
});

describe('user coordination authorization', function (): void {
    test('assignment requires an authenticated request', function (string $method): void {
        $coordination = userCoordinationRecord();
        $user = User::factory()->create(['coordination_id' => $coordination['id']]);
        $uri = $method === 'POST' ? '/api/users' : '/api/users/'.$user->uuid;

        $response = $this->json($method, $uri, [
            'name' => 'Changed User', 'email' => 'changed@example.com', 'password' => 'changed-password',
            'coordination_id' => $coordination['id'],
        ]);

        $response->assertUnauthorized();
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseMissing('users', ['email' => 'changed@example.com']);
    })->with(['creation' => ['POST'], 'update' => ['PATCH']]);

    test('non-administrative roles are forbidden from assigning a coordination to another user', function (UserRoleEnum $role, string $method): void {
        $coordination = userCoordinationRecord();
        $user = User::factory()->withRole($role)->create(['coordination_id' => $coordination['id']]);
        $target = User::factory()->withRole(UserRoleEnum::VIEWER)->create();
        $accessToken = userCoordinationAccessToken($user);
        $uri = $method === 'POST' ? '/api/users' : '/api/users/'.$target->uuid;

        $response = $this->withToken($accessToken)->json($method, $uri, [
            'name' => 'Changed User', 'email' => 'changed@example.com', 'password' => 'changed-password',
            'coordination_id' => $coordination['id'],
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseMissing('users', ['email' => 'changed@example.com']);
        $this->assertDatabaseHas('users', ['id' => $target->id, 'coordination_id' => null]);
    })->with([
        'operator' => [UserRoleEnum::OPERATOR], 'reviewer' => [UserRoleEnum::REVIEWER], 'viewer' => [UserRoleEnum::VIEWER],
    ])->with(['creation' => ['POST'], 'update' => ['PATCH']]);

    test('adding a coordination field to a self password change is forbidden for every non-administrative role', function (UserRoleEnum $role, bool $explicitNull): void {
        $coordination = userCoordinationRecord();
        $newCoordination = userCoordinationRecord();
        $user = User::factory()->withRole($role)->create(['coordination_id' => $coordination['id']]);
        $accessToken = userCoordinationAccessToken($user);

        $response = $this->withToken($accessToken)->patchJson('/api/users/'.$user->uuid, [
            'password' => 'changed-password', 'coordination_id' => $explicitNull ? null : $newCoordination['id'],
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'coordination_id' => $coordination['id']]);
        $this->assertDatabaseHas('user_access_tokens', ['token' => hash('sha256', $accessToken)]);
        expect(Hash::check('password', $user->refresh()->password))->toBeTrue();
    })->with([
        'operator' => [UserRoleEnum::OPERATOR], 'reviewer' => [UserRoleEnum::REVIEWER], 'viewer' => [UserRoleEnum::VIEWER],
    ])->with(['null' => [true], 'identifier' => [false]]);

    test('a required password change does not allow changing the coordination', function (): void {
        $coordination = userCoordinationRecord();
        $administrator = User::factory()->superuser()->mustChangePassword()->create();
        $accessToken = userCoordinationAccessToken($administrator);

        $response = $this->withToken($accessToken)->patchJson('/api/users/'.$administrator->uuid, [
            'password' => 'changed-password', 'coordination_id' => $coordination['id'],
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $administrator->id, 'coordination_id' => null, 'must_change_password' => true]);
        expect(Hash::check('password', $administrator->refresh()->password))->toBeTrue();
    });
});

describe('legacy users and coordination persistence', function (): void {
    test('legacy writers without a coordination can still change their password', function (UserRoleEnum $role): void {
        $user = User::factory()->withRole($role)->mustChangePassword()->create();
        $accessToken = userCoordinationAccessToken($user);

        $response = $this->withToken($accessToken)->postJson('/api/auth/change-password', [
            'current_password' => 'password', 'password' => 'changed-password', 'password_confirmation' => 'changed-password',
        ]);

        $response->assertOk()->assertJsonPath('data.user.coordination_id', null)->assertJsonPath('data.user.coordination', null);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'coordination_id' => null, 'must_change_password' => false]);
        expect(Hash::check('changed-password', $user->refresh()->password))->toBeTrue();
    })->with(['operator' => [UserRoleEnum::OPERATOR], 'reviewer' => [UserRoleEnum::REVIEWER]]);

    test('administrative edits must repair missing legacy writer coordinations', function (): void {
        $coordination = userCoordinationRecord();
        $administrator = User::factory()->superuser()->create();
        $user = User::factory()->create(['name' => 'Original User']);
        $accessToken = userCoordinationAccessToken($administrator);

        $this->withToken($accessToken)->patchJson('/api/users/'.$user->uuid, ['name' => 'Changed User'])
            ->assertUnprocessable()->assertJsonValidationErrors('coordination_id');
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Original User', 'coordination_id' => null]);

        $this->withToken($accessToken)->patchJson('/api/users/'.$user->uuid, ['name' => 'Changed User', 'coordination_id' => $coordination['id']])
            ->assertOk()->assertJsonPath('data.coordination', $coordination);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Changed User', 'coordination_id' => $coordination['id']]);
    });

    test('the coordination migration leaves legacy users unassigned and preserves them during rollback', function (): void {
        $migration = require database_path('migrations/Identity/2026_10_08_200401_add_coordination_id_to_users_table.php');
        $migration->down();
        $attributes = [
            'uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'name' => 'Legacy User',
            'email' => 'legacy@example.com',
            'password' => Hash::make('password'),
            'created_at' => '2026-09-30 12:00:00',
        ];
        $userId = DB::table('users')->insertGetId($attributes);
        $originalAttributes = (array) DB::table('users')->where('id', $userId)->first();

        $migration->up();

        $this->assertDatabaseHas('users', [...$originalAttributes, 'coordination_id' => null]);
        $migration->down();

        expect(Schema::hasColumn('users', 'coordination_id'))->toBeFalse();
        $this->assertDatabaseHas('users', $originalAttributes);
        $this->assertDatabaseCount('users', 1);
    });

    test('the user coordination foreign key rejects missing records', function (): void {
        expect(fn () => User::factory()->create(['coordination_id' => 99999]))->toThrow(QueryException::class);

        $this->assertDatabaseEmpty('users');
    });

    test('referenced coordinations cannot be deleted or have their identifiers changed', function (string $operation): void {
        $coordination = userCoordinationRecord();
        $user = User::factory()->create(['coordination_id' => $coordination['id']]);
        $query = DB::table('coordinations')->where('id', $coordination['id']);

        expect(fn () => $operation === 'delete' ? $query->delete() : $query->update(['id' => 99999]))
            ->toThrow(QueryException::class);

        $this->assertDatabaseHas('coordinations', $coordination);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'coordination_id' => $coordination['id']]);
    })->with(['delete' => ['delete'], 'change identifier' => ['update']]);
});
