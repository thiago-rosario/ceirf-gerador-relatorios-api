<?php

declare(strict_types=1);

use Illuminate\Cache\RateLimiter as CacheRateLimiter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use src\Modules\Identity\Domain\Enum\UserRoleEnum;
use src\Modules\Identity\Model\Role;
use src\Modules\Identity\Model\User;

function identityAccessToken(User $user): string
{
    $response = test()->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertOk();
    Auth::forgetGuards();

    return $response->json('data.access_token');
}

describe('authentication', function (): void {
    test('login issues a hashed bearer token and returns the public UUID', function (): void {
        $user = User::factory()->create();

        $response = $this->postJson('/api/auth/login', [
            'email' => strtoupper($user->email),
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.user.id', $user->uuid)
            ->assertJsonPath('data.user.email', $user->email)
            ->assertJsonPath('data.user.role', UserRoleEnum::OPERATOR->value)
            ->assertJsonMissingPath('data.user.password');
        $accessToken = $response->json('data.access_token');
        expect($accessToken)->toBeString()->not->toBeEmpty();
        $this->assertDatabaseHas('user_access_tokens', [
            'user_id' => $user->id,
            'token' => hash('sha256', $accessToken),
        ]);
        $this->assertDatabaseMissing('user_access_tokens', ['token' => $accessToken]);
    });

    test('login returns 401 for incorrect credentials without creating tokens', function (): void {
        $user = User::factory()->create();

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertUnauthorized()->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Credenciais inválidas.');
        $this->assertDatabaseCount('user_access_tokens', 0);
    });

    test('login returns 401 for inactive users without creating tokens', function (): void {
        $user = User::factory()->inactive()->create();

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertUnauthorized()->assertJsonPath('message', 'Credenciais inválidas.');
        $this->assertDatabaseCount('user_access_tokens', 0);
    });

    test('an unexpected login failure returns 500, reports the exception and creates no token', function (): void {
        $user = User::factory()->create();
        $exception = new RuntimeException('User retrieval failed.');
        Exceptions::fake();
        User::retrieved(function (User $retrievedUser) use ($user, $exception): void {
            if ($retrievedUser->id === $user->id) {
                throw $exception;
            }
        });

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertInternalServerError()->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'An unexpected error occurred');
        $this->assertDatabaseCount('user_access_tokens', 0);
        Exceptions::assertReported(fn (RuntimeException $reportedException): bool => $reportedException === $exception);
    });

    test('login responds to browser preflight without requiring authentication', function (): void {
        $response = $this->options('/api/auth/login', headers: [
            'Origin' => 'http://localhost:5173',
            'Access-Control-Request-Method' => 'POST',
            'Access-Control-Request-Headers' => 'content-type',
        ]);

        $response->assertNoContent()
            ->assertHeader('Access-Control-Allow-Origin', '*')
            ->assertHeader('Access-Control-Allow-Methods', 'POST')
            ->assertHeader('Access-Control-Allow-Headers', 'content-type');
        $this->assertDatabaseCount('user_access_tokens', 0);
    });

    test('login returns the CORS header required by the frontend browser', function (): void {
        $user = User::factory()->create();

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ], ['Origin' => 'http://localhost:5173']);

        $response->assertOk()->assertHeader('Access-Control-Allow-Origin', '*');
        $this->assertDatabaseHas('user_access_tokens', ['user_id' => $user->id]);
    });

    test('login returns 422 when required credentials are missing', function (): void {
        $response = $this->postJson('/api/auth/login');

        $response->assertUnprocessable()->assertJsonValidationErrors([
            'email' => 'The email field is required.',
            'password' => 'The password field is required.',
        ]);
        $this->assertDatabaseCount('user_access_tokens', 0);
    });

    test('login returns 422 for an array email without creating tokens', function (): void {
        $response = $this->postJson('/api/auth/login', [
            'email' => ['ana@example.com'],
            'password' => 'password',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors([
            'email' => 'The email field must be a string.',
        ]);
        $this->assertDatabaseCount('user_access_tokens', 0);
    });

    test('the sixth login attempt within a minute returns 429 despite email case changes', function (): void {
        $this->freezeTime();
        $user = User::factory()->create();
        $credentials = ['email' => $user->email, 'password' => 'wrong-password'];
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/auth/login', $credentials)->assertUnauthorized();
        }

        $response = $this->postJson('/api/auth/login', [
            ...$credentials,
            'email' => ' '.strtoupper($user->email).' ',
        ]);

        $response->assertTooManyRequests()->assertHeader('Retry-After', '60');
        $this->assertDatabaseCount('user_access_tokens', 0);
    });

    test('login succeeds and the sixth attempt returns 429 with the configured database cache', function (): void {
        $this->freezeTime();
        config(['cache.default' => 'database']);
        $loginLimiter = RateLimiter::limiter('auth-login');
        $rateLimiter = new CacheRateLimiter(Cache::store());
        $rateLimiter->for('auth-login', $loginLimiter);
        RateLimiter::swap($rateLimiter);
        $user = User::factory()->create();
        $credentials = ['email' => $user->email, 'password' => 'password'];
        $this->postJson('/api/auth/login', $credentials)->assertOk();
        for ($attempt = 0; $attempt < 4; $attempt++) {
            $this->postJson('/api/auth/login', [...$credentials, 'password' => 'wrong-password'])->assertUnauthorized();
        }

        $response = $this->postJson('/api/auth/login', $credentials);

        $response->assertTooManyRequests()->assertHeader('Retry-After', '60');
        $this->assertDatabaseCount('cache', 2);
        $this->assertDatabaseCount('user_access_tokens', 1);
    });

    test('protected endpoints return 401 when a bearer token is missing', function (string $method, string $uri): void {
        $response = $this->json($method, $uri);

        $response->assertUnauthorized()->assertJsonPath('status', 'error');
        $this->assertDatabaseCount('users', 0);
    })->with([
        'create user' => ['POST', '/api/users'],
        'list users' => ['GET', '/api/users'],
        'search users' => ['GET', '/api/users/search?name=Ana'],
        'find user' => ['GET', '/api/users/550e8400-e29b-41d4-a716-446655440000'],
        'update user' => ['PATCH', '/api/users/550e8400-e29b-41d4-a716-446655440000'],
        'deactivate user' => ['PATCH', '/api/users/550e8400-e29b-41d4-a716-446655440000/deactivate'],
        'reset password' => ['POST', '/api/auth/reset-password/550e8400-e29b-41d4-a716-446655440000'],
        'logout' => ['POST', '/api/auth/logout'],
        'current user' => ['GET', '/api/auth/me'],
    ]);

    test('protected endpoints return 401 when the bearer token is invalid', function (): void {
        $response = $this->withToken('invalid-token')->getJson('/api/users');

        $response->assertUnauthorized()->assertJsonPath('status', 'error');
    });

    test('session validation returns the current public user for every role and ignores client criteria', function (UserRoleEnum $role): void {
        $user = User::factory()->withRole($role)->create();
        $otherUser = User::factory()->create();
        $accessToken = identityAccessToken($user);
        $user->update(['name' => 'Updated User']);

        $response = $this->withToken($accessToken)->getJson('/api/auth/me?id='.$otherUser->uuid.'&email='.$otherUser->email);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.user.id', $user->uuid)
            ->assertJsonPath('data.user.name', 'Updated User')
            ->assertJsonPath('data.user.role', $role->value)
            ->assertJsonPath('data.user.must_change_password', false)
            ->assertExactJsonStructure([
                'status',
                'data' => ['user' => ['id', 'name', 'email', 'role', 'is_active', 'created_at', 'must_change_password']],
            ]);
        $this->assertDatabaseCount('user_access_tokens', 1);
    })->with(UserRoleEnum::cases());

    test('session validation returns 401 for an invalid bearer token', function (): void {
        $response = $this->withToken('invalid-token')->getJson('/api/auth/me');

        $response->assertUnauthorized()->assertJsonPath('message', 'Não autenticado.');
    });

    test('session validation returns 401 after the current token is revoked', function (): void {
        $user = User::factory()->create();
        $accessToken = identityAccessToken($user);
        $this->withToken($accessToken)->postJson('/api/auth/logout')->assertOk();
        Auth::forgetGuards();

        $response = $this->withToken($accessToken)->getJson('/api/auth/me');

        $response->assertUnauthorized()->assertJsonPath('message', 'Não autenticado.');
    });

    test('session validation returns 401 after the user becomes inactive', function (): void {
        $user = User::factory()->create();
        $accessToken = identityAccessToken($user);
        $user->update(['is_active' => false]);

        $response = $this->withToken($accessToken)->getJson('/api/auth/me');

        $response->assertUnauthorized()->assertJsonPath('message', 'Não autenticado.');
    });

    test('session validation remains accessible when a password change is required', function (): void {
        $user = User::factory()->mustChangePassword()->create();
        $accessToken = identityAccessToken($user);

        $response = $this->withToken($accessToken)->getJson('/api/auth/me');

        $response->assertOk()->assertJsonPath('data.user.must_change_password', true);
        $this->assertDatabaseCount('user_access_tokens', 1);
    });

    test('logout revokes only the current token and preserves another session', function (): void {
        $user = User::factory()->superuser()->create();
        $currentToken = identityAccessToken($user);
        $otherToken = identityAccessToken($user);

        $response = $this->withToken($currentToken)->postJson('/api/auth/logout', ['access_token' => $otherToken]);

        $response->assertOk()->assertJsonPath('status', 'success');
        $this->assertDatabaseMissing('user_access_tokens', ['token' => hash('sha256', $currentToken)]);
        $this->assertDatabaseHas('user_access_tokens', ['token' => hash('sha256', $otherToken)]);
        Auth::forgetGuards();
        $this->withToken($currentToken)->getJson('/api/users')->assertUnauthorized();
        Auth::forgetGuards();
        $this->withToken($otherToken)->getJson('/api/users')->assertOk();
    });
});

describe('user administration', function (): void {
    test('a superuser creates a user with a UUID, hashed password and assigned role', function (): void {
        $administrator = User::factory()->superuser()->create();
        $accessToken = identityAccessToken($administrator);

        $response = $this->withToken($accessToken)->postJson('/api/users', [
            'name' => 'Ana Silva',
            'email' => ' ANA@example.com ',
            'password' => 'new-password',
            'role' => UserRoleEnum::REVIEWER->value,
            'is_active' => false,
            'uuid' => '550e8400-e29b-41d4-a716-446655440000',
        ]);

        $response->assertCreated()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.name', 'Ana Silva')
            ->assertJsonPath('data.email', 'ana@example.com')
            ->assertJsonPath('data.role', UserRoleEnum::REVIEWER->value)
            ->assertJsonPath('data.is_active', true)
            ->assertExactJsonStructure([
                'status',
                'data' => ['id', 'name', 'email', 'role', 'is_active', 'created_at'],
            ]);
        $createdUser = User::query()->where('email', 'ana@example.com')->firstOrFail();
        expect(Str::isUuid($response->json('data.id')))->toBeTrue();
        expect($createdUser->uuid)->toBe($response->json('data.id'));
        expect($createdUser->uuid)->not->toBe('550e8400-e29b-41d4-a716-446655440000');
        expect($createdUser->role)->toBe(UserRoleEnum::REVIEWER);
        expect(Hash::check('new-password', $createdUser->password))->toBeTrue();
        expect($createdUser->password)->not->toBe('new-password');
        $this->assertDatabaseCount('users', 2);
    });

    test('a password resembling a stored bcrypt hash is hashed as literal input on creation', function (): void {
        $administrator = User::factory()->superuser()->create();
        $accessToken = identityAccessToken($administrator);
        $literalPassword = Hash::make('inner-password');

        $response = $this->withToken($accessToken)->postJson('/api/users', [
            'name' => 'Ana Silva',
            'email' => 'ana@example.com',
            'password' => $literalPassword,
        ]);

        $response->assertCreated()->assertJsonPath('data.email', 'ana@example.com');
        $createdUser = User::query()->where('email', 'ana@example.com')->firstOrFail();
        expect(Hash::check($literalPassword, $createdUser->password))->toBeTrue();
        expect(Hash::check('inner-password', $createdUser->password))->toBeFalse();
        expect($createdUser->password)->not->toBe($literalPassword);
    });

    test('user creation returns 422 for missing required fields and saves nothing', function (): void {
        $administrator = User::factory()->superuser()->create();
        $accessToken = identityAccessToken($administrator);

        $response = $this->withToken($accessToken)->postJson('/api/users');

        $response->assertUnprocessable()->assertJsonValidationErrors([
            'name' => 'The name field is required.',
            'email' => 'The email field is required.',
            'password' => 'The password field is required.',
        ]);
        $this->assertDatabaseCount('users', 1);
    });

    test('user creation returns 422 for duplicate normalized emails and saves nothing', function (): void {
        $administrator = User::factory()->superuser()->create(['email' => 'admin@example.com']);
        $accessToken = identityAccessToken($administrator);

        $response = $this->withToken($accessToken)->postJson('/api/users', [
            'name' => 'Other Administrator',
            'email' => ' ADMIN@example.com ',
            'password' => 'new-password',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors([
            'email' => 'The email has already been taken.',
        ]);
        $this->assertDatabaseCount('users', 1);
    });

    test('user creation returns 422 for an invalid role and saves nothing', function (): void {
        $administrator = User::factory()->superuser()->create();
        $accessToken = identityAccessToken($administrator);

        $response = $this->withToken($accessToken)->postJson('/api/users', [
            'name' => 'Ana Silva',
            'email' => 'ana@example.com',
            'password' => 'new-password',
            'role' => 'ADMIN',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors([
            'role' => 'The selected role is invalid.',
        ]);
        $this->assertDatabaseCount('users', 1);
    });

    test('user creation returns 422 for values longer than their database columns', function (string $field, string $value, string $message): void {
        $administrator = User::factory()->superuser()->create();
        $accessToken = identityAccessToken($administrator);
        $attributes = [
            'name' => 'Ana Silva',
            'email' => 'ana@example.com',
            'password' => 'new-password',
        ];
        $attributes[$field] = $value;

        $response = $this->withToken($accessToken)->postJson('/api/users', $attributes);

        $response->assertUnprocessable()->assertJsonValidationErrors([$field => $message]);
        $this->assertDatabaseCount('users', 1);
    })->with([
        'name over 100 characters' => ['name', str_repeat('a', 101), 'The name field must not be greater than 100 characters.'],
        'email over 150 characters' => ['email', str_repeat('a', 60).'@'.str_repeat('b', 60).'.'.str_repeat('c', 25).'.com', 'The email field must not be greater than 150 characters.'],
    ]);

    test('a superuser retrieves a user by UUID without exposing credentials', function (): void {
        $administrator = User::factory()->superuser()->create();
        $user = User::factory()->create();
        $accessToken = identityAccessToken($administrator);

        $response = $this->withToken($accessToken)->getJson('/api/users/'.$user->uuid);

        $response->assertOk()->assertJsonPath('status', 'success')
            ->assertJsonPath('data.id', $user->uuid)
            ->assertJsonPath('data.email', $user->email)
            ->assertExactJsonStructure([
                'status',
                'data' => ['id', 'name', 'email', 'role', 'is_active', 'created_at', 'must_change_password'],
            ]);
    });

    test('finding a missing UUID returns 404', function (): void {
        $administrator = User::factory()->superuser()->create();
        $accessToken = identityAccessToken($administrator);

        $response = $this->withToken($accessToken)->getJson('/api/users/550e8400-e29b-41d4-a716-446655440000');

        $response->assertNotFound()->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Usuário não encontrado.');
    });

    test('finding a numeric identifier returns 422 instead of exposing an internal database ID', function (): void {
        $administrator = User::factory()->superuser()->create();
        $accessToken = identityAccessToken($administrator);

        $response = $this->withToken($accessToken)->getJson('/api/users/'.$administrator->id);

        $response->assertUnprocessable()->assertJsonValidationErrors([
            'id' => 'The id field must be a valid UUID.',
        ]);
    });

    test('a superuser searches by one exact criterion', function (string $criterion): void {
        $administrator = User::factory()->superuser()->create();
        $user = User::factory()->create(['name' => 'Ana Silva', 'email' => 'ana@example.com']);
        $accessToken = identityAccessToken($administrator);
        $value = $criterion === 'id' ? $user->uuid : $user->{$criterion};

        $response = $this->withToken($accessToken)->getJson('/api/users/search?'.http_build_query([$criterion => $value]));

        $response->assertOk()->assertJsonPath('data.id', $user->uuid);
    })->with(['UUID' => ['id'], 'name' => ['name'], 'email' => ['email']]);

    test('search returns 422 without exactly one criterion', function (string $query): void {
        $administrator = User::factory()->superuser()->create();
        $accessToken = identityAccessToken($administrator);

        $response = $this->withToken($accessToken)->getJson('/api/users/search'.$query);

        $response->assertUnprocessable();
    })->with(['missing criteria' => [''], 'multiple criteria' => ['?name=Ana&email=ana@example.com']]);

    test('a superuser lists filtered users in the requested order', function (): void {
        $administrator = User::factory()->superuser()->create(['name' => 'Administrator']);
        $firstUser = User::factory()->create(['name' => 'Ana Silva']);
        $secondUser = User::factory()->create(['name' => 'Ana Souza']);
        $accessToken = identityAccessToken($administrator);

        $response = $this->withToken($accessToken)->getJson('/api/users?filter=Ana&order_by=ASC');

        $response->assertOk()->assertJsonPath('status', 'success')
            ->assertJsonCount(2, 'data.users')
            ->assertJsonPath('data.users.0.id', $firstUser->uuid)
            ->assertJsonPath('data.users.1.id', $secondUser->uuid)
            ->assertJsonMissingPath('data.users.0.password');
    });

    test('user listing returns 422 for an injected order direction', function (): void {
        $administrator = User::factory()->superuser()->create();
        $accessToken = identityAccessToken($administrator);

        $response = $this->withToken($accessToken)->getJson('/api/users?'.http_build_query(['order_by' => 'ASC; DROP TABLE users']));

        $response->assertUnprocessable()->assertJsonValidationErrors([
            'order_by' => 'The selected order by is invalid.',
        ]);
        $this->assertModelExists($administrator);
    });

    test('non-administrative roles receive 403 on user administration', function (UserRoleEnum $role): void {
        $user = User::factory()->withRole($role)->create();
        $accessToken = identityAccessToken($user);

        $response = $this->withToken($accessToken)->getJson('/api/users');

        $response->assertForbidden()->assertJsonPath('status', 'error');
    })->with([
        'operator' => [UserRoleEnum::OPERATOR],
        'viewer' => [UserRoleEnum::VIEWER],
        'reviewer' => [UserRoleEnum::REVIEWER],
    ]);

    test('a superuser updates user data and hashes a changed password', function (): void {
        $administrator = User::factory()->superuser()->create();
        $user = User::factory()->create();
        $accessToken = identityAccessToken($administrator);

        $response = $this->withToken($accessToken)->patchJson('/api/users/'.$user->uuid, [
            'name' => 'Updated User',
            'email' => ' UPDATED@example.com ',
            'password' => 'updated-password',
            'role' => UserRoleEnum::VIEWER->value,
        ]);

        $response->assertOk()->assertJsonPath('data.id', $user->uuid)
            ->assertJsonPath('data.email', 'updated@example.com')
            ->assertJsonPath('data.role', UserRoleEnum::VIEWER->value)
            ->assertJsonMissingPath('data.password');
        $user->refresh();
        expect($user->name)->toBe('Updated User');
        expect($user->email)->toBe('updated@example.com');
        expect($user->role)->toBe(UserRoleEnum::VIEWER);
        expect(Hash::check('updated-password', $user->password))->toBeTrue();
    });

    test('an empty user update returns 422 without changing the user', function (): void {
        $administrator = User::factory()->superuser()->create();
        $user = User::factory()->create(['name' => 'Ana Silva']);
        $originalPassword = $user->password;
        $accessToken = identityAccessToken($administrator);

        $response = $this->withToken($accessToken)->patchJson('/api/users/'.$user->uuid);

        $response->assertUnprocessable();
        expect($user->refresh()->name)->toBe('Ana Silva');
        expect($user->password)->toBe($originalPassword);
    });

    test('updating a missing UUID returns 404 without creating a user', function (): void {
        $administrator = User::factory()->superuser()->create();
        $accessToken = identityAccessToken($administrator);

        $response = $this->withToken($accessToken)->patchJson('/api/users/550e8400-e29b-41d4-a716-446655440000', [
            'name' => 'Missing User',
        ]);

        $response->assertNotFound()->assertJsonPath('message', 'Usuário não encontrado.');
        $this->assertDatabaseCount('users', 1);
    });

    test('updating another user to an existing email returns 422 without changes', function (): void {
        $administrator = User::factory()->superuser()->create(['email' => 'admin@example.com']);
        $user = User::factory()->create(['email' => 'ana@example.com']);
        $accessToken = identityAccessToken($administrator);

        $response = $this->withToken($accessToken)->patchJson('/api/users/'.$user->uuid, ['email' => 'ADMIN@example.com']);

        $response->assertUnprocessable()->assertJsonValidationErrors([
            'email' => 'The email has already been taken.',
        ]);
        expect($user->refresh()->email)->toBe('ana@example.com');
    });

    test('a user changes only their own password and clears the required-change flag', function (): void {
        $user = User::factory()->mustChangePassword()->create();
        $accessToken = identityAccessToken($user);

        $response = $this->withToken($accessToken)->patchJson('/api/users/'.$user->uuid, ['password' => 'changed-password']);

        $response->assertOk()->assertJsonPath('status', 'success');
        $user->refresh();
        expect($user->must_change_password)->toBeFalse();
        expect(Hash::check('changed-password', $user->password))->toBeTrue();
        $this->assertDatabaseMissing('user_access_tokens', ['user_id' => $user->id]);
        Auth::forgetGuards();
        $this->withToken($accessToken)->postJson('/api/auth/logout')->assertUnauthorized();
        Auth::forgetGuards();
        $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'changed-password'])
            ->assertOk()->assertJsonPath('data.user.must_change_password', false);
    });

    test('changing a password preserves every existing role assignment', function (): void {
        $user = User::factory()->withRole(UserRoleEnum::REVIEWER)->create();
        $reviewerRole = Role::forRole(UserRoleEnum::REVIEWER);
        $viewerRole = Role::forRole(UserRoleEnum::VIEWER);
        $user->roles()->attach($viewerRole->id);
        $accessToken = identityAccessToken($user);

        $response = $this->withToken($accessToken)->patchJson('/api/users/'.$user->uuid, ['password' => 'changed-password']);

        $response->assertOk()->assertJsonPath('data.role', UserRoleEnum::REVIEWER->value);
        expect(Hash::check('changed-password', $user->refresh()->password))->toBeTrue();
        $this->assertDatabaseHas('user_roles', ['user_id' => $user->id, 'role_id' => $reviewerRole->id]);
        $this->assertDatabaseHas('user_roles', ['user_id' => $user->id, 'role_id' => $viewerRole->id]);
        $this->assertDatabaseCount('user_roles', 2);
    });

    test('a user can change and log in with a password that resembles a stored bcrypt hash', function (): void {
        $user = User::factory()->create();
        $accessToken = identityAccessToken($user);
        $literalPassword = Hash::make('inner-password');

        $response = $this->withToken($accessToken)->patchJson('/api/users/'.$user->uuid, ['password' => $literalPassword]);

        $response->assertOk();
        expect(Hash::check($literalPassword, $user->refresh()->password))->toBeTrue();
        expect(Hash::check('inner-password', $user->password))->toBeFalse();
        $this->assertDatabaseMissing('user_access_tokens', ['user_id' => $user->id]);
        Auth::forgetGuards();
        $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => $literalPassword])
            ->assertOk()->assertJsonPath('data.user.id', $user->uuid);
    });

    test('a user receives 403 when attempting to elevate their own role', function (): void {
        $user = User::factory()->create();
        $accessToken = identityAccessToken($user);

        $response = $this->withToken($accessToken)->patchJson('/api/users/'.$user->uuid, [
            'password' => 'changed-password',
            'role' => UserRoleEnum::SUPERUSER->value,
        ]);

        $response->assertForbidden();
        expect($user->refresh()->role)->toBe(UserRoleEnum::OPERATOR);
        expect(Hash::check('password', $user->password))->toBeTrue();
    });

    test('a user receives 403 when changing another user password', function (): void {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $accessToken = identityAccessToken($user);

        $response = $this->withToken($accessToken)->patchJson('/api/users/'.$otherUser->uuid, ['password' => 'changed-password']);

        $response->assertForbidden();
        expect(Hash::check('password', $otherUser->refresh()->password))->toBeTrue();
    });

    test('deactivation prevents login and revokes every target user token', function (): void {
        $administrator = User::factory()->superuser()->create();
        $user = User::factory()->create();
        $targetToken = identityAccessToken($user);
        identityAccessToken($user);
        $accessToken = identityAccessToken($administrator);

        $response = $this->withToken($accessToken)->patchJson('/api/users/'.$user->uuid.'/deactivate');

        $response->assertOk()->assertJsonPath('data.id', $user->uuid)
            ->assertJsonPath('data.is_active', false);
        expect($user->refresh()->is_active)->toBeFalse();
        $this->assertDatabaseMissing('user_access_tokens', ['user_id' => $user->id]);
        $this->assertDatabaseHas('user_access_tokens', ['user_id' => $administrator->id]);
        Auth::forgetGuards();
        $this->withToken($targetToken)->postJson('/api/auth/logout')->assertUnauthorized();
        Auth::forgetGuards();
        $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'password'])->assertUnauthorized();
    });

    test('deactivating a missing UUID returns 404 without changing the administrator', function (): void {
        $administrator = User::factory()->superuser()->create();
        $accessToken = identityAccessToken($administrator);

        $response = $this->withToken($accessToken)->patchJson('/api/users/550e8400-e29b-41d4-a716-446655440000/deactivate');

        $response->assertNotFound()->assertJsonPath('message', 'Usuário não encontrado.');
        expect($administrator->refresh()->is_active)->toBeTrue();
        $this->assertDatabaseHas('user_access_tokens', ['token' => hash('sha256', $accessToken)]);
    });
});

describe('password reset', function (): void {
    test('a superuser resets a password, requires its change and revokes previous sessions', function (): void {
        $administrator = User::factory()->superuser()->create();
        $user = User::factory()->create();
        $targetToken = identityAccessToken($user);
        identityAccessToken($user);
        $accessToken = identityAccessToken($administrator);

        $response = $this->withToken($accessToken)->postJson('/api/auth/reset-password/'.$user->uuid);

        $response->assertOk()->assertJsonPath('status', 'success')
            ->assertJsonMissingPath('data.password');
        $user->refresh();
        expect($user->must_change_password)->toBeTrue();
        expect(Hash::check('sspba123', $user->password))->toBeTrue();
        expect($user->password)->not->toBe('sspba123');
        $this->assertDatabaseMissing('user_access_tokens', ['user_id' => $user->id]);
        $this->assertDatabaseHas('user_access_tokens', ['user_id' => $administrator->id]);
        Auth::forgetGuards();
        $this->withToken($targetToken)->postJson('/api/auth/logout')->assertUnauthorized();
        Auth::forgetGuards();
        $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'sspba123'])
            ->assertOk()->assertJsonPath('data.user.must_change_password', true);
    });

    test('an operator receives 403 when attempting to reset another password', function (): void {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $accessToken = identityAccessToken($user);

        $response = $this->withToken($accessToken)->postJson('/api/auth/reset-password/'.$otherUser->uuid);

        $response->assertForbidden();
        expect(Hash::check('password', $otherUser->refresh()->password))->toBeTrue();
        expect($otherUser->must_change_password)->toBeFalse();
    });

    test('resetting a missing UUID returns 404 without revoking another user session', function (): void {
        $administrator = User::factory()->superuser()->create();
        $accessToken = identityAccessToken($administrator);

        $response = $this->withToken($accessToken)->postJson('/api/auth/reset-password/550e8400-e29b-41d4-a716-446655440000');

        $response->assertNotFound()->assertJsonPath('message', 'Usuário não encontrado.');
        expect($administrator->refresh()->must_change_password)->toBeFalse();
        $this->assertDatabaseHas('user_access_tokens', ['token' => hash('sha256', $accessToken)]);
    });

    test('a failed reset returns 500 and preserves the password and existing sessions', function (): void {
        $administrator = User::factory()->superuser()->create();
        $user = User::factory()->create();
        $originalPassword = $user->password;
        $targetToken = identityAccessToken($user);
        $accessToken = identityAccessToken($administrator);
        $exception = new RuntimeException('Password persistence failed.');
        Exceptions::fake();
        User::updated(function (User $updatedUser) use ($user, $exception): void {
            if ($updatedUser->id === $user->id) {
                throw $exception;
            }
        });

        $response = $this->withToken($accessToken)->postJson('/api/auth/reset-password/'.$user->uuid);

        $response->assertInternalServerError()->assertJsonPath('status', 'error');
        expect($user->refresh()->password)->toBe($originalPassword);
        expect($user->must_change_password)->toBeFalse();
        $this->assertDatabaseHas('user_access_tokens', ['token' => hash('sha256', $targetToken)]);
        Exceptions::assertReported(fn (RuntimeException $reportedException): bool => $reportedException === $exception);
    });

    test('a superuser requiring a password change receives 403 on administrative routes', function (): void {
        $administrator = User::factory()->superuser()->mustChangePassword()->create();
        $accessToken = identityAccessToken($administrator);

        $response = $this->withToken($accessToken)->getJson('/api/users');

        $response->assertForbidden()->assertJsonPath('status', 'error');
    });

    test('a superuser requiring a password change receives 403 for an update with other fields', function (): void {
        $administrator = User::factory()->superuser()->mustChangePassword()->create(['name' => 'Administrator']);
        $accessToken = identityAccessToken($administrator);

        $response = $this->withToken($accessToken)->patchJson('/api/users/'.$administrator->uuid, [
            'password' => 'changed-password',
            'name' => 'Updated Administrator',
        ]);

        $response->assertForbidden();
        expect($administrator->refresh()->name)->toBe('Administrator');
        expect($administrator->must_change_password)->toBeTrue();
        expect(Hash::check('password', $administrator->password))->toBeTrue();
    });

    test('a user requiring a password change can log out', function (): void {
        $user = User::factory()->mustChangePassword()->create();
        $accessToken = identityAccessToken($user);

        $response = $this->withToken($accessToken)->postJson('/api/auth/logout');

        $response->assertOk()->assertJsonPath('status', 'success');
        $this->assertDatabaseMissing('user_access_tokens', ['token' => hash('sha256', $accessToken)]);
    });
});
