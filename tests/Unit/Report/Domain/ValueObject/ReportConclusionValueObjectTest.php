<?php

declare(strict_types=1);

use src\Modules\Report\Domain\Exception\InvalidReportConclusionException;
use src\Modules\Report\Domain\Validation\ReportConclusionValidation;
use src\Modules\Report\Domain\ValueObject\ReportConclusionValueObject;

test('allows an unfinished conclusion while the report is a draft', function (): void {
    $conclusion = new ReportConclusionValueObject;

    expect($conclusion->content())->toBe('');
});

test('rejects generation without a conclusion', function (string $content): void {
    $conclusion = new ReportConclusionValueObject($content);

    expect(fn () => ReportConclusionValidation::validateForGeneration($conclusion))
        ->toThrow(function (InvalidReportConclusionException $exception): void {
            expect($exception->getCode())->toBe(2018);
            expect($exception->getMessage())->toBe('A conclusão deve estar preenchida para gerar o documento.');
        });
})->with([
    'empty' => [''],
    'spaces' => ['   '],
    'Unicode whitespace' => ["\u{2003}"],
]);

test('preserves conclusion content without imposing an undefined numeric limit', function (): void {
    $content = str_repeat('O terreno apresenta viabilidade técnica. ', 1000);
    $conclusion = new ReportConclusionValueObject($content);

    ReportConclusionValidation::validateForGeneration($conclusion);

    expect($conclusion->content())->toBe($content);
});
