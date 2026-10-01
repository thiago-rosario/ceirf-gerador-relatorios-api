<?php

declare(strict_types=1);

use src\Identity\Domain\Entity\UserEntity;
use src\Identity\Domain\Enum\UserRoleEnum;
use src\Identity\Domain\Exception\InvalidEmailException;
use src\Identity\Domain\Exception\InvalidUserIdException;
use src\Identity\Domain\Exception\UserNameCannotBeEmptyException;
use src\Identity\Domain\Exception\UserPasswordCannotBeEmptyException;
use src\Identity\Domain\Resolver\UuidResolver;
use src\Identity\Domain\ValueObject\EmailValueObject;

test('creates users of every role with any structurally valid email', function (UserRoleEnum $role, string $address): void {
    $id = new UuidResolver('550e8400-e29b-41d4-a716-446655440000');
    $email = new EmailValueObject($address);

    $user = new UserEntity(id: $id, name: '  Ana Silva  ', email: $email, password: 'secret', role: $role);

    expect($user->id())->toBe($id);
    expect($user->name())->toBe('Ana Silva');
    expect($user->email())->toBe($email);
    expect($user->password())->toBe('secret');
    expect($user->role())->toBe($role);
    expect($user->isActive())->toBeTrue();
    expect($user->mustChangePassword())->toBeFalse();
})->with([
    'operator' => [UserRoleEnum::OPERATOR, 'ana@example.com'],
    'viewer' => [UserRoleEnum::VIEWER, 'viewer@example.org'],
    'reviewer' => [UserRoleEnum::REVIEWER, 'reviewer@example.net'],
    'superuser' => [UserRoleEnum::SUPERUSER, 'admin@example.com'],
    'normalized superuser' => [UserRoleEnum::SUPERUSER, ' ADMIN@EXAMPLE.ORG '],
]);

test('normalizes primitive identity values and defaults to the operator role', function (): void {
    $user = new UserEntity(
        id: '550e8400-e29b-41d4-a716-446655440000',
        name: '  Ana  ',
        email: ' ANA@EXAMPLE.COM ',
        password: 'secret',
    );

    expect($user->id()->value())->toBe('550e8400-e29b-41d4-a716-446655440000');
    expect($user->name())->toBe('Ana');
    expect($user->email()->value())->toBe('ana@example.com');
    expect($user->role())->toBe(UserRoleEnum::OPERATOR);
});

test('generates a valid UUID when no identity is supplied', function (): void {
    $user = new UserEntity(name: 'Ana', email: 'ana@example.com', password: 'secret');

    expect($user->id()->value())->toBeUuid();
});

test('rejects invalid UUIDs through the resolver', function (string $id): void {
    expect(fn () => new UserEntity(id: $id, name: 'Ana', email: 'ana@example.com', password: 'secret'))
        ->toThrow(function (InvalidUserIdException $exception) use ($id): void {
            expect($exception->getCode())->toBe(1003);
            expect($exception->getMessage())->toBe(sprintf('A classe <%s> não permite o valor <%s>.', UuidResolver::class, $id));
        });
})->with(['empty' => [''], 'malformed' => ['invalid-uuid']]);

test('changes roles independently of the current email', function (UserRoleEnum $initial, UserRoleEnum $target): void {
    $user = new UserEntity(name: 'Ana', email: 'ana@example.com', password: 'secret', role: $initial);

    $user->changeRole($target);

    expect($user->role())->toBe($target);
    expect($user->email()->value())->toBe('ana@example.com');
})->with([
    'operator to viewer' => [UserRoleEnum::OPERATOR, UserRoleEnum::VIEWER],
    'viewer to reviewer' => [UserRoleEnum::VIEWER, UserRoleEnum::REVIEWER],
    'operator to superuser' => [UserRoleEnum::OPERATOR, UserRoleEnum::SUPERUSER],
    'reviewer to superuser' => [UserRoleEnum::REVIEWER, UserRoleEnum::SUPERUSER],
    'superuser to operator' => [UserRoleEnum::SUPERUSER, UserRoleEnum::OPERATOR],
]);

test('changes and normalizes emails for every role including superusers', function (UserRoleEnum $role): void {
    $user = new UserEntity(name: 'Ana', email: 'ana@example.com', password: 'secret', role: $role);

    $user->changeEmail(' NEW@EXAMPLE.ORG ');

    expect($user->email()->value())->toBe('new@example.org');
    expect($user->role())->toBe($role);
})->with(UserRoleEnum::cases());

test('preserves the complete state after rejecting a structurally invalid email', function (string $address): void {
    $user = new UserEntity(
        name: 'Ana', email: 'ana@example.com', password: 'secret', role: UserRoleEnum::SUPERUSER,
        updatedAt: '2000-01-01 00:00:00',
    );
    $email = $user->email();
    $updatedAt = $user->updatedAt();

    expect(fn () => $user->changeEmail($address))->toThrow(InvalidEmailException::class);

    expect($user->email())->toBe($email);
    expect($user->role())->toBe(UserRoleEnum::SUPERUSER);
    expect($user->updatedAt())->toBe($updatedAt);
})->with(['empty' => [''], 'spaces' => ['   '], 'malformed' => ['invalid-email']]);

test('accepts an email value object when changing email', function (): void {
    $user = new UserEntity(name: 'Ana', email: 'ana@example.com', password: 'secret');
    $email = new EmailValueObject('new@example.org');

    $user->changeEmail($email);

    expect($user->email())->toBe($email);
});

test('changes and trims a nonempty name without altering identity', function (): void {
    $id = new UuidResolver('550e8400-e29b-41d4-a716-446655440000');
    $user = new UserEntity(id: $id, name: 'Ana', email: 'ana@example.com', password: 'secret');

    $user->changeName('  A  ');

    expect($user->name())->toBe('A');
    expect($user->id())->toBe($id);
});

test('rejects empty names during creation and preserves the name and date on failed changes', function (string $name): void {
    $user = new UserEntity(name: 'Ana', email: 'ana@example.com', password: 'secret', updatedAt: '2000-01-01 00:00:00');
    $updatedAt = $user->updatedAt();

    expect(fn () => new UserEntity(name: $name, email: 'ana@example.com', password: 'secret'))
        ->toThrow(function (UserNameCannotBeEmptyException $exception): void {
            expect($exception->getCode())->toBe(1001);
            expect($exception->getMessage())->toBe('O nome do usuário não pode estar vazio.');
        });
    expect(fn () => $user->changeName($name))->toThrow(UserNameCannotBeEmptyException::class, 'O nome do usuário não pode estar vazio.');
    expect($user->name())->toBe('Ana');
    expect($user->updatedAt())->toBe($updatedAt);
})->with([
    'empty' => [''],
    'spaces' => ['   '],
    'line breaks' => ["\t\n"],
    'nonbreaking space' => ["\u{00A0}"],
    'em space' => ["\u{2003}"],
]);

test('accepts nonempty names without imposing length or character restrictions', function (string $name): void {
    $user = new UserEntity(name: $name, email: 'ana@example.com', password: 'secret');

    $user->changeName($name);

    expect($user->name())->toBe($name);
})->with([
    'one character' => ['A'],
    'zero' => ['0'],
    'digits' => ['123'],
    'accents and special characters' => ["João D'Ávila #1"],
    'long name' => [str_repeat('Ana', 100)],
]);

test('rejects an empty password during creation', function (): void {
    expect(fn () => new UserEntity(name: 'Ana', email: 'ana@example.com', password: ''))
        ->toThrow(function (UserPasswordCannotBeEmptyException $exception): void {
            expect($exception->getCode())->toBe(1002);
            expect($exception->getMessage())->toBe('A senha do usuário não pode estar vazia.');
        });
});

test('accepts nonempty passwords and clears the required change flag after a normal change', function (string $password): void {
    $user = new UserEntity(name: 'Ana', email: 'ana@example.com', password: $password);

    expect($user->password())->toBe($password);

    $user->resetPassword();
    $user->changePassword($password);

    expect($user->password())->toBe($password);
    expect($user->mustChangePassword())->toBeFalse();
})->with([
    'one character' => ['a'],
    'zero' => ['0'],
    'spaces are preserved' => ['   '],
    'surrounding whitespace is preserved' => [' secret '],
    'long password' => [str_repeat('a', 300)],
]);

test('resets the password to the default and requires a subsequent change', function (): void {
    $user = new UserEntity(name: 'Ana', email: 'ana@example.com', password: 'secret');

    $user->resetPassword();

    expect($user->password())->toBe('sspba123');
    expect($user->mustChangePassword())->toBeTrue();

    $user->validate();
});

test('preserves the password, required change flag and date after rejecting an empty password', function (bool $mustChangePassword): void {
    $user = new UserEntity(
        name: 'Ana', email: 'ana@example.com', password: 'secret', mustChangePassword: $mustChangePassword,
        updatedAt: '2000-01-01 00:00:00',
    );
    $updatedAt = $user->updatedAt();

    expect(fn () => $user->changePassword(''))->toThrow(UserPasswordCannotBeEmptyException::class, 'A senha do usuário não pode estar vazia.');

    expect($user->password())->toBe('secret');
    expect($user->mustChangePassword())->toBe($mustChangePassword);
    expect($user->updatedAt())->toBe($updatedAt);
})->with(['required' => [true], 'not required' => [false]]);

test('accepts every active and required password change state', function (bool $isActive, bool $mustChangePassword): void {
    $user = new UserEntity(
        name: 'Ana', email: 'ana@example.com', password: 'secret',
        isActive: $isActive, mustChangePassword: $mustChangePassword,
    );

    $user->validate();

    expect($user->isActive())->toBe($isActive);
    expect($user->mustChangePassword())->toBe($mustChangePassword);
})->with([
    'active without pending change' => [true, false],
    'active with pending change' => [true, true],
    'inactive without pending change' => [false, false],
    'inactive with pending change' => [false, true],
]);

test('initializes both dates when creating a user without timestamps', function (): void {
    $user = new UserEntity(name: 'Ana', email: 'ana@example.com', password: 'secret');

    expect($user->createdAt())->toBeInstanceOf(DateTimeImmutable::class);
    expect($user->updatedAt())->toBe($user->createdAt());
});

test('accepts stored dates without imposing additional date rules', function (): void {
    $user = new UserEntity(
        name: 'Ana', email: 'ana@example.com', password: 'secret',
        createdAt: '2040-01-01 12:00:00', updatedAt: '2030-01-01 12:00:00',
    );

    expect($user->createdAt()->format('Y-m-d H:i:s'))->toBe('2040-01-01 12:00:00');
    expect($user->updatedAt()->format('Y-m-d H:i:s'))->toBe('2030-01-01 12:00:00');
});

test('protects the dates from changes to mutable constructor inputs', function (): void {
    $createdAt = new DateTime('2026-09-30 10:00:00');
    $updatedAt = new DateTime('2026-09-30 11:00:00');
    $user = new UserEntity(
        name: 'Ana', email: 'ana@example.com', password: 'secret',
        createdAt: $createdAt, updatedAt: $updatedAt,
    );

    $createdAt->modify('+1 day');
    $updatedAt->modify('+1 day');

    expect($user->createdAt()->format('Y-m-d H:i:s'))->toBe('2026-09-30 10:00:00');
    expect($user->updatedAt()->format('Y-m-d H:i:s'))->toBe('2026-09-30 11:00:00');
});

test('updates the modification date after every relevant state change', function (string $method, array $arguments, bool $isActive): void {
    $user = new UserEntity(
        name: 'Ana', email: 'ana@example.com', password: 'secret', isActive: $isActive,
        createdAt: '2000-01-01 10:00:00', updatedAt: '2000-01-01 11:00:00',
    );

    $user->{$method}(...$arguments);

    expect($user->updatedAt()->format('Y-m-d H:i:s'))->not->toBe('2000-01-01 11:00:00');
    expect($user->createdAt()->format('Y-m-d H:i:s'))->toBe('2000-01-01 10:00:00');
})->with([
    'name change' => ['changeName', ['Maria'], true],
    'email change' => ['changeEmail', ['maria@example.org'], true],
    'role change' => ['changeRole', [UserRoleEnum::REVIEWER], true],
    'password reset' => ['resetPassword', [], true],
    'password change' => ['changePassword', ['new-secret'], true],
    'activation' => ['activate', [], false],
    'deactivation' => ['deactivate', [], true],
]);

test('allows active state transitions without modifying the date when repeating the same transition', function (bool $initial, string $method, bool $target): void {
    $user = new UserEntity(name: 'Ana', email: 'ana@example.com', password: 'secret', isActive: $initial);

    $user->{$method}();

    expect($user->isActive())->toBe($target);
    $updatedAt = $user->updatedAt();

    $user->{$method}();
    $user->validate();

    expect($user->isActive())->toBe($target);
    expect($user->updatedAt())->toBe($updatedAt);
})->with([
    'activation' => [false, 'activate', true],
    'deactivation' => [true, 'deactivate', false],
]);
