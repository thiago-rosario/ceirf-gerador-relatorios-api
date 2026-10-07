<?php

use Database\Seeders\Identity\UserSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use src\Modules\Identity\Model\User;

test('development seeding creates an active account with the requested password and role', function (string $email, string $role): void {
    $this->seed(UserSeeder::class);

    $this->assertDatabaseCount('users', 4);
    $this->assertDatabaseCount('user_roles', 4);
    $user = User::query()->where('email', $email)->firstOrFail();
    expect(Str::isUuid($user->uuid))->toBeTrue();
    expect($user->password)->not->toBe('senha');
    expect(Hash::check('senha', $user->password))->toBeTrue();

    $this->postJson('/api/auth/login', ['email' => $email, 'password' => 'senha'])
        ->assertOk()
        ->assertJsonPath('data.user.id', $user->uuid)
        ->assertJsonPath('data.user.role', $role)
        ->assertJsonPath('data.user.must_change_password', false);
})->with([
    'operator' => ['operador@ceirf.test', 'OPERATOR'],
    'viewer' => ['viewer@ceirf.test', 'VIEWER'],
    'reviewer' => ['reviewer@ceirf.test', 'REVIEWER'],
    'superuser' => ['superusuario@ceirf.test', 'SUPERUSER'],
]);

test('repeating development seeding preserves user IDs and unrelated accounts without duplicating roles', function (): void {
    $existingUser = User::factory()->inactive()->mustChangePassword()->create([
        'email' => 'existing@ceirf.test',
    ]);
    $existingPassword = $existingUser->password;
    $this->seed(UserSeeder::class);
    $identifiers = User::query()->orderBy('email')->get(['id', 'uuid'])->toArray();

    $this->seed(UserSeeder::class);

    $this->assertDatabaseCount('users', 5);
    $this->assertDatabaseCount('user_roles', 5);
    $this->assertDatabaseCount('roles', 4);
    expect(User::query()->orderBy('email')->get(['id', 'uuid'])->toArray())->toBe($identifiers);
    $existingUser->refresh();
    expect($existingUser->password)->toBe($existingPassword);
    expect($existingUser->is_active)->toBeFalse();
    expect($existingUser->must_change_password)->toBeTrue();
});

test('development accounts are not seeded outside local and testing environments', function (string $environment): void {
    app()->detectEnvironment(fn (): string => $environment);

    $this->artisan('db:seed', ['--class' => UserSeeder::class, '--force' => true])->assertSuccessful();

    $this->assertDatabaseEmpty('users');
    $this->assertDatabaseEmpty('user_roles');
    $this->assertDatabaseEmpty('roles');
})->with(['production', 'staging']);
