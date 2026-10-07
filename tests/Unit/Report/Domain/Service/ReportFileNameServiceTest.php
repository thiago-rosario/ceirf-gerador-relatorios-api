<?php

declare(strict_types=1);

use src\Modules\Report\Domain\Entity\ReportEntity;
use src\Modules\Report\Domain\Enum\ForceEnum;
use src\Modules\Report\Domain\Enum\ReportSizeEnum;
use src\Modules\Report\Domain\Exception\IncompleteReportException;
use src\Modules\Report\Domain\Exception\InvalidGeneratedReportException;
use src\Modules\Report\Domain\ValueObject\MunicipalityValueObject;
use src\Modules\Report\Domain\ValueObject\ReportCoverValueObject;
use src\Modules\Report\Domain\ValueObject\SeiNumberValueObject;

test('composes PDF names from cover content without requiring completed sections', function (string $municipality, string $typology, ForceEnum $force, ReportSizeEnum $size, string $expected): void {
    $report = new ReportEntity(
        createdBy: '550e8400-e29b-41d4-a716-446655440000',
        cover: new ReportCoverValueObject(
            municipality: new MunicipalityValueObject(1, $municipality),
            force: $force,
            size: $size,
            typology: $typology,
            seiNumber: new SeiNumberValueObject('12345.67890/2026-01'),
        ),
    );

    expect($report->fileName())->toBe($expected);
    expect($report->isGenerated())->toBeFalse();
})->with([
    'original' => ['Salvador', 'Delegacia', ForceEnum::PM, ReportSizeEnum::ONE_B, 'SALVADOR_PM_1B_DELEGACIA.pdf'],
    'municipality accents' => ['Vitória da Conquista', 'Unidade Integrada', ForceEnum::PC_PM, ReportSizeEnum::ONE_B_ONE_A, 'VITÓRIA_DA_CONQUISTA_PC-PM_1B-1A_UNIDADE_INTEGRADA.pdf'],
    'no standard size' => ['Salvador', '  Posto   de Atendimento  ', ForceEnum::SSP, ReportSizeEnum::WITHOUT_STANDARD, 'SALVADOR_SSP_SEM_PADRÃO_POSTO_DE_ATENDIMENTO.pdf'],
    'reserved characters' => ['Salvador', '../Unidade\\Nova:*?"|<>', ForceEnum::CBM, ReportSizeEnum::ONE_A, 'SALVADOR_CBM_1A_UNIDADE_NOVA.pdf'],
]);

test('rejects PDF naming when the cover is missing or incomplete', function (?ReportCoverValueObject $cover): void {
    $report = new ReportEntity(createdBy: '550e8400-e29b-41d4-a716-446655440000', cover: $cover);

    expect(fn () => $report->fileName())->toThrow(function (IncompleteReportException $exception): void {
        expect($exception->getCode())->toBe(2005);
    });
})->with([
    'missing' => [null],
    'partial' => [new ReportCoverValueObject(typology: 'Delegacia')],
]);

test('rejects a typology that cannot produce a meaningful filename component', function (): void {
    $report = new ReportEntity(
        createdBy: '550e8400-e29b-41d4-a716-446655440000',
        cover: new ReportCoverValueObject(new MunicipalityValueObject(1, 'Salvador'), ForceEnum::PM, ReportSizeEnum::ONE_B, '../*?', new SeiNumberValueObject('123')),
    );

    expect(fn () => $report->fileName())->toThrow(function (InvalidGeneratedReportException $exception): void {
        expect($exception->getCode())->toBe(2022);
    });
});
