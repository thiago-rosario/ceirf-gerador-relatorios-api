<?php

declare(strict_types=1);

use src\Modules\Identity\Domain\Exception\InvalidEmailException;
use src\Modules\Identity\Domain\ValueObject\EmailValueObject;

test('accepts and normalizes structurally valid email addresses', function (string $input, string $expected) {
    $email = new EmailValueObject($input);

    expect($email->value())->toBe($expected);
    expect((string) $email)->toBe($expected);
})->with([
    'external address' => ['person@example.com', 'person@example.com'],
    'whitespace and uppercase' => ['  PERSON@EXAMPLE.COM  ', 'person@example.com'],
    'plus addressing' => ['Person+tag@Example.com', 'person+tag@example.com'],
]);

test('rejects structurally invalid email addresses', function (string $input): void {
    expect(fn () => new EmailValueObject($input))->toThrow(function (InvalidEmailException $exception): void {
        expect($exception->getCode())->toBe(1004);
        expect($exception->getMessage())->toBe('O e-mail deve possuir uma estrutura válida.');
    });
})->with([
    'empty' => [''],
    'whitespace' => ['   '],
    'missing domain' => ['person@'],
    'missing local part' => ['@example.com'],
    'missing separator' => ['person.example.com'],
    'internal whitespace' => ['per son@example.com'],
    'multiple separators' => ['person@@example.com'],
]);

test('compares normalized email values', function () {
    $email = new EmailValueObject('Person@Example.com');

    expect($email->equals(new EmailValueObject(' person@example.com ')))->toBeTrue();
    expect($email->equals(new EmailValueObject('other@example.com')))->toBeFalse();
});
