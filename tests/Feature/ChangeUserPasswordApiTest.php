<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Hash;
use src\Modules\Identity\Application\Exception\InvalidCredentialsException;
use src\Modules\Identity\Domain\Entity\UserEntity;
use src\Modules\Identity\Domain\Enum\UserRoleEnum;
use src\Modules\Identity\Infra\Repositories\Auth\CreateAccessTokenEloquentRepository;
use src\Modules\Identity\Model\Role;
use src\Modules\Identity\Model\User;

function passwordChangeAccessToken(User $user, string $password = 'password'): string
{
    $response = test()->postJson('/api/auth/login', ['email' => $user->email, 'password' => $password]);
    $response->assertOk();
    Auth::forgetGuards();

    return $response->json('data.access_token');
}

test('every role can replace its temporary password while keeping the current session', function (UserRoleEnum $role): void {
    $user = User::factory()->withRole($role)->mustChangePassword()->create();
    $currentToken = passwordChangeAccessToken($user);
    $otherToken = passwordChangeAccessToken($user);

    $response = $this->withToken($currentToken)->postJson('/api/auth/change-password', [
        'current_password' => 'password',
        'password' => 'chosen-password',
        'password_confirmation' => 'chosen-password',
    ]);

    $response->assertOk()->assertJsonPath('status', 'success')
        ->assertJsonPath('data.user.id', $user->uuid)
        ->assertJsonPath('data.user.role', $role->value)
        ->assertJsonPath('data.user.must_change_password', false)
        ->assertExactJsonStructure([
            'status',
            'data' => ['user' => ['id', 'name', 'email', 'role', 'is_active', 'created_at', 'must_change_password', 'coordination_id', 'coordination']],
        ]);
    expect(Hash::check('chosen-password', $user->refresh()->password))->toBeTrue();
    $this->assertDatabaseHas('users', ['id' => $user->id, 'must_change_password' => false]);
    $this->assertDatabaseHas('user_access_tokens', ['token' => hash('sha256', $currentToken)]);
    $this->assertDatabaseMissing('user_access_tokens', ['token' => hash('sha256', $otherToken)]);
    Auth::forgetGuards();
    $this->withToken($currentToken)->getJson('/api/auth/me')->assertOk()->assertJsonPath('data.user.must_change_password', false);
    Auth::forgetGuards();
    $this->withToken($otherToken)->getJson('/api/auth/me')->assertUnauthorized();
    Auth::forgetGuards();
    $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'password'])->assertUnauthorized();
    Auth::forgetGuards();
    $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'chosen-password'])->assertOk();
})->with(UserRoleEnum::cases());

test('an admin reset followed by login and password change preserves coordination and all role assignments', function (): void {
    $coordination = ['code' => 'CEIRF', 'name' => 'Coordenação CEIRF'];
    $coordinationId = DB::table('coordinations')->insertGetId([...$coordination, 'is_active' => true]);
    $administrator = User::factory()->superuser()->create();
    $user = User::factory()->withRole(UserRoleEnum::REVIEWER)->create(['coordination_id' => $coordinationId]);
    $reviewerRole = Role::forRole(UserRoleEnum::REVIEWER);
    $viewerRole = Role::forRole(UserRoleEnum::VIEWER);
    $user->roles()->attach($viewerRole->id);
    $previousToken = passwordChangeAccessToken($user);
    $adminToken = passwordChangeAccessToken($administrator);
    $this->withToken($adminToken)->postJson('/api/auth/reset-password/'.$user->uuid)->assertOk();
    $this->assertDatabaseMissing('user_access_tokens', ['token' => hash('sha256', $previousToken)]);
    Auth::forgetGuards();
    $temporaryToken = passwordChangeAccessToken($user, 'sspba123');
    $this->withToken($temporaryToken)->getJson('/api/auth/me')->assertOk()->assertJsonPath('data.user.must_change_password', true);

    $response = $this->withToken($temporaryToken)->postJson('/api/auth/change-password', [
        'current_password' => 'sspba123',
        'password' => 'senha-escolhida',
        'password_confirmation' => 'senha-escolhida',
    ]);

    $response->assertOk()->assertJsonPath('data.user.must_change_password', false)
        ->assertJsonPath('data.user.coordination_id', $coordinationId)
        ->assertJsonPath('data.user.coordination', ['id' => $coordinationId, ...$coordination]);
    expect(Hash::check('senha-escolhida', $user->refresh()->password))->toBeTrue();
    $this->assertDatabaseHas('user_roles', ['user_id' => $user->id, 'role_id' => $reviewerRole->id]);
    $this->assertDatabaseHas('user_roles', ['user_id' => $user->id, 'role_id' => $viewerRole->id]);
    $this->assertDatabaseHas('user_access_tokens', ['token' => hash('sha256', $adminToken)]);
});

test('password change returns 401 for missing or invalid authentication', function (?string $token): void {
    $response = $this->withHeader('Authorization', $token === null ? '' : 'Bearer '.$token)->postJson('/api/auth/change-password', [
        'current_password' => 'password', 'password' => 'chosen-password', 'password_confirmation' => 'chosen-password',
    ]);

    $response->assertUnauthorized()->assertJsonPath('message', 'Não autenticado.');
    $this->assertDatabaseCount('users', 0);
    $this->assertDatabaseCount('user_access_tokens', 0);
})->with(['missing' => [null], 'invalid' => ['invalid-token']]);

test('password change returns 401 after the account is deactivated', function (): void {
    $user = User::factory()->mustChangePassword()->create();
    $token = passwordChangeAccessToken($user);
    $user->update(['is_active' => false]);

    $response = $this->withToken($token)->postJson('/api/auth/change-password', [
        'current_password' => 'password', 'password' => 'chosen-password', 'password_confirmation' => 'chosen-password',
    ]);

    $response->assertUnauthorized();
    expect(Hash::check('password', $user->refresh()->password))->toBeTrue();
    expect($user->must_change_password)->toBeTrue();
});

test('invalid password changes return 422 and preserve the pending restriction and sessions', function (array $changes, array $errors): void {
    $user = User::factory()->mustChangePassword()->create();
    $currentToken = passwordChangeAccessToken($user);
    $otherToken = passwordChangeAccessToken($user);
    $payload = array_replace([
        'current_password' => 'password', 'password' => 'chosen-password', 'password_confirmation' => 'chosen-password',
    ], $changes);

    $response = $this->withToken($currentToken)->postJson('/api/auth/change-password', $payload);

    $response->assertUnprocessable()->assertJsonValidationErrors($errors);
    expect(Hash::check('password', $user->refresh()->password))->toBeTrue();
    expect($user->must_change_password)->toBeTrue();
    $this->assertDatabaseHas('user_access_tokens', ['token' => hash('sha256', $currentToken)]);
    $this->assertDatabaseHas('user_access_tokens', ['token' => hash('sha256', $otherToken)]);
})->with([
    'missing fields' => [
        ['current_password' => null, 'password' => null, 'password_confirmation' => null],
        ['current_password' => 'Informe a senha atual.', 'password' => 'Informe a nova senha.', 'password_confirmation' => 'Confirme a nova senha.'],
    ],
    'incorrect current password' => [['current_password' => 'wrong-password'], ['current_password' => 'A senha atual está incorreta.']],
    'short new password' => [['password' => '1234567', 'password_confirmation' => '1234567'], ['password' => 'A nova senha deve ter pelo menos 8 caracteres.']],
    'mismatched confirmation' => [['password_confirmation' => 'different-password'], ['password' => 'A confirmação da nova senha não confere.']],
    'missing confirmation' => [['password_confirmation' => null], ['password_confirmation' => 'Confirme a nova senha.']],
    'reused temporary password' => [['password' => 'password', 'password_confirmation' => 'password'], ['password' => 'A nova senha deve ser diferente da senha atual.']],
    'array current password' => [['current_password' => ['password']], ['current_password' => 'A senha atual deve ser um texto.']],
    'array new password' => [['password' => ['chosen-password']], ['password' => 'A nova senha deve ser um texto.']],
    'array confirmation' => [['password_confirmation' => ['chosen-password']], ['password_confirmation' => 'A confirmação da nova senha deve ser um texto.']],
]);

test('a user with a pending change cannot bypass password verification using the user update endpoint', function (): void {
    $user = User::factory()->mustChangePassword()->create();
    $token = passwordChangeAccessToken($user);

    $response = $this->withToken($token)->patchJson('/api/users/'.$user->uuid, ['password' => 'password']);

    $response->assertForbidden()->assertJsonPath('data.must_change_password', true);
    expect($user->refresh()->must_change_password)->toBeTrue();
    expect(Hash::check('password', $user->password))->toBeTrue();
    $this->assertDatabaseHas('user_access_tokens', ['token' => hash('sha256', $token)]);
});

test('password change ignores client identity and permissions and only updates the authenticated user', function (): void {
    $user = User::factory()->mustChangePassword()->create();
    $otherUser = User::factory()->mustChangePassword()->create();
    $token = passwordChangeAccessToken($user);

    $response = $this->withToken($token)->postJson('/api/auth/change-password', [
        'id' => $otherUser->uuid, 'must_change_password' => false, 'role' => UserRoleEnum::SUPERUSER->value,
        'name' => 'Attempted Name', 'email' => $otherUser->email, 'coordination_id' => 999,
        'current_password' => 'password', 'password' => 'chosen-password', 'password_confirmation' => 'chosen-password',
    ]);

    $response->assertOk()->assertJsonPath('data.user.id', $user->uuid)
        ->assertJsonPath('data.user.role', UserRoleEnum::OPERATOR->value)
        ->assertJsonPath('data.user.name', $user->name)
        ->assertJsonPath('data.user.email', $user->email)
        ->assertJsonPath('data.user.coordination_id', null);
    expect(Hash::check('chosen-password', $user->refresh()->password))->toBeTrue();
    expect(Hash::check('password', $otherUser->refresh()->password))->toBeTrue();
    expect($otherUser->must_change_password)->toBeTrue();
});

test('the sixth password change attempt per account and IP within a minute returns 429', function (): void {
    $this->freezeTime();
    $user = User::factory()->mustChangePassword()->create();
    $token = passwordChangeAccessToken($user);
    $payload = ['current_password' => 'wrong-password', 'password' => 'chosen-password', 'password_confirmation' => 'chosen-password'];
    for ($attempt = 0; $attempt < 5; $attempt++) {
        $this->withToken($token)->postJson('/api/auth/change-password', $payload)->assertUnprocessable();
    }

    $response = $this->withToken($token)->postJson('/api/auth/change-password', $payload);

    $response->assertTooManyRequests()->assertHeader('Retry-After', '60');
    expect(Hash::check('password', $user->refresh()->password))->toBeTrue();
    expect($user->must_change_password)->toBeTrue();
});

test('a failed password change returns 500 and rolls back the password, restriction and session revocations', function (): void {
    $user = User::factory()->mustChangePassword()->create();
    $currentToken = passwordChangeAccessToken($user);
    $otherToken = passwordChangeAccessToken($user);
    $exception = new RuntimeException('Password persistence failed.');
    Exceptions::fake();
    User::updated(function (User $updatedUser) use ($user, $exception): void {
        if ($updatedUser->id === $user->id) {
            throw $exception;
        }
    });

    $response = $this->withToken($currentToken)->postJson('/api/auth/change-password', [
        'current_password' => 'password', 'password' => 'chosen-password', 'password_confirmation' => 'chosen-password',
    ]);

    $response->assertInternalServerError()->assertJsonPath('status', 'error');
    expect(Hash::check('password', $user->refresh()->password))->toBeTrue();
    expect($user->must_change_password)->toBeTrue();
    $this->assertDatabaseHas('user_access_tokens', ['token' => hash('sha256', $currentToken)]);
    $this->assertDatabaseHas('user_access_tokens', ['token' => hash('sha256', $otherToken)]);
    Exceptions::assertReported(fn (RuntimeException $reportedException): bool => $reportedException === $exception);
});

test('a chosen password resembling a bcrypt hash is hashed as literal input', function (): void {
    $user = User::factory()->create();
    $token = passwordChangeAccessToken($user);
    $literalPassword = Hash::make('inner-password');

    $response = $this->withToken($token)->postJson('/api/auth/change-password', [
        'current_password' => 'password', 'password' => $literalPassword, 'password_confirmation' => $literalPassword,
    ]);

    $response->assertOk()->assertJsonPath('data.user.must_change_password', false);
    expect(Hash::check($literalPassword, $user->refresh()->password))->toBeTrue();
    expect(Hash::check('inner-password', $user->password))->toBeFalse();
});

test('a login authenticated before a password change cannot issue a surviving session afterward', function (): void {
    $user = User::factory()->mustChangePassword()->create();
    $token = passwordChangeAccessToken($user);
    $previouslyAuthenticatedUser = UserEntity::fromModel($user->load('roles'));
    $this->withToken($token)->postJson('/api/auth/change-password', [
        'current_password' => 'password', 'password' => 'chosen-password', 'password_confirmation' => 'chosen-password',
    ])->assertOk();

    expect(fn () => app(CreateAccessTokenEloquentRepository::class)->createAccessToken($previouslyAuthenticatedUser))
        ->toThrow(InvalidCredentialsException::class, 'Credenciais inválidas.');

    $this->assertDatabaseCount('user_access_tokens', 1);
    $this->assertDatabaseHas('user_access_tokens', ['token' => hash('sha256', $token)]);
});

test('login returns 401 without issuing tokens when the account changes during authentication', function (string $changedField): void {
    $user = User::factory()->create();
    $passwordHash = Hash::make('chosen-password');
    $changedDuringAuthentication = false;
    User::retrieved(function (User $retrievedUser) use ($user, $changedField, $passwordHash, &$changedDuringAuthentication): void {
        if ($retrievedUser->id === $user->id && ! $changedDuringAuthentication) {
            $changedDuringAuthentication = true;
            DB::table('users')->where('id', $user->id)->update([
                $changedField => $changedField === 'password' ? $passwordHash : false,
            ]);
        }
    });

    $response = $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'password']);

    $response->assertUnauthorized()->assertJsonPath('message', 'Credenciais inválidas.');
    $this->assertDatabaseCount('user_access_tokens', 0);
})->with(['password replaced' => ['password'], 'account deactivated' => ['is_active']]);
