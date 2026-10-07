<?php

declare(strict_types=1);

use src\Modules\Report\Domain\Enum\ForceEnum;
use src\Modules\Report\Domain\Enum\ReportSizeEnum;
use src\Modules\Report\Domain\Exception\IncompleteReportException;
use src\Modules\Report\Domain\Exception\InvalidMunicipalityException;
use src\Modules\Report\Domain\Exception\InvalidReportTypologyException;
use src\Modules\Report\Domain\Exception\InvalidSeiNumberException;
use src\Modules\Report\Domain\Validation\ReportCoverValidation;
use src\Modules\Report\Domain\ValueObject\MunicipalityValueObject;
use src\Modules\Report\Domain\ValueObject\ReportCoverValueObject;
use src\Modules\Report\Domain\ValueObject\SeiNumberValueObject;

test('allows an empty cover while the report is a draft', function (): void {
    $cover = new ReportCoverValueObject;

    expect($cover->municipality())->toBeNull();
    expect($cover->force())->toBeNull();
    expect($cover->size())->toBeNull();
    expect($cover->typology())->toBe('');
    expect($cover->seiNumber())->toBeNull();
});

test('accepts a complete cover with a municipality from Bahia for generation', function (): void {
    $municipality = new MunicipalityValueObject(2927408, 'Salvador', ' ba ');
    $seiNumber = new SeiNumberValueObject('001.0001.2026.0000001-01');
    $cover = new ReportCoverValueObject($municipality, ForceEnum::PM, ReportSizeEnum::ONE_B, 'Companhia', $seiNumber);

    ReportCoverValidation::validateForGeneration($cover);

    expect($cover->municipality())->toBe($municipality);
    expect($municipality->id())->toBe(2927408);
    expect($municipality->name())->toBe('Salvador');
    expect($municipality->stateCode())->toBe('BA');
    expect($cover->force())->toBe(ForceEnum::PM);
    expect($cover->size())->toBe(ReportSizeEnum::ONE_B);
    expect($cover->typology())->toBe('Companhia');
    expect($cover->seiNumber())->toBe($seiNumber);
});

test('rejects generation when a required cover field is missing', function (string $field, ?string $value): void {
    $arguments = [
        'municipality' => new MunicipalityValueObject(2927408, 'Salvador'),
        'force' => ForceEnum::PM,
        'size' => ReportSizeEnum::ONE_B,
        'typology' => 'Companhia',
        'seiNumber' => new SeiNumberValueObject('001.0001.2026.0000001-01'),
    ];
    $arguments[$field] = $value;
    $cover = new ReportCoverValueObject(...$arguments);

    expect(fn () => ReportCoverValidation::validateForGeneration($cover))
        ->toThrow(function (IncompleteReportException $exception): void {
            expect($exception->getCode())->toBe(2005);
            expect($exception->getMessage())->toBe('A capa deve possuir município, força, tamanho, tipologia e número SEI para gerar o relatório.');
        });
})->with([
    'municipality' => ['municipality', null],
    'force' => ['force', null],
    'size' => ['size', null],
    'typology' => ['typology', ''],
    'whitespace typology' => ['typology', "\u{2003}"],
    'SEI number' => ['seiNumber', null],
]);

test('rejects invalid municipality references and municipalities outside Bahia', function (int $id, string $name, string $stateCode): void {
    expect(fn () => new MunicipalityValueObject($id, $name, $stateCode))
        ->toThrow(function (InvalidMunicipalityException $exception): void {
            expect($exception->getCode())->toBe(2006);
            expect($exception->getMessage())->toBe('O município deve ser uma referência válida ao catálogo de municípios da Bahia.');
        });
})->with([
    'zero identifier' => [0, 'Salvador', 'BA'],
    'negative identifier' => [-1, 'Salvador', 'BA'],
    'missing name' => [2927408, '', 'BA'],
    'Unicode blank name' => [2927408, "\u{2003}", 'BA'],
    'oversized name' => [2927408, str_repeat('á', 101), 'BA'],
    'outside Bahia' => [3550308, 'São Paulo', 'SP'],
    'missing state' => [2927408, 'Salvador', ''],
]);

test('accepts a typology with exactly 100 multibyte characters', function (): void {
    $typology = str_repeat('á', 100);

    $cover = new ReportCoverValueObject(typology: $typology);

    expect($cover->typology())->toBe($typology);
});

test('rejects a typology beyond 100 characters even in a draft', function (): void {
    expect(fn () => new ReportCoverValueObject(typology: str_repeat('á', 101)))
        ->toThrow(function (InvalidReportTypologyException $exception): void {
            expect($exception->getCode())->toBe(2007);
            expect($exception->getMessage())->toBe('A tipologia deve possuir no máximo 100 caracteres.');
        });
});

test('accepts a SEI number with exactly 80 multibyte characters', function (): void {
    $value = str_repeat('á', 80);

    $seiNumber = new SeiNumberValueObject($value);

    expect($seiNumber->value())->toBe($value);
    expect((string) $seiNumber)->toBe($value);
});

test('rejects blank or oversized SEI numbers', function (string $value): void {
    expect(fn () => new SeiNumberValueObject($value))
        ->toThrow(function (InvalidSeiNumberException $exception): void {
            expect($exception->getCode())->toBe(2008);
            expect($exception->getMessage())->toBe('O número SEI deve estar preenchido e possuir no máximo 80 caracteres.');
        });
})->with([
    'empty' => [''],
    'spaces' => ['   '],
    'Unicode whitespace' => ["\u{2003}"],
    '81 multibyte characters' => [str_repeat('á', 81)],
]);
