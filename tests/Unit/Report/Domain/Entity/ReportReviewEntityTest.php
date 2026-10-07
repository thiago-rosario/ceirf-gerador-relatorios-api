<?php

declare(strict_types=1);

use src\Modules\Report\Domain\Entity\ReportReviewEntity;
use src\Modules\Report\Domain\Enum\ReportReviewDecisionEnum;
use src\Modules\Report\Domain\Exception\InvalidReportDateException;
use src\Modules\Report\Domain\Exception\InvalidReportIdException;

test('records independent decisions by multiple human reviewers for the same document version', function (): void {
    $reportId = '550e8400-e29b-41d4-a716-446655440010';

    $approval = new ReportReviewEntity(
        reportId: $reportId,
        reviewerId: '550e8400-e29b-41d4-a716-446655440001',
        decision: ReportReviewDecisionEnum::APPROVED,
        comment: 'Documento conferido.',
    );
    $rejection = new ReportReviewEntity(
        reportId: $reportId,
        reviewerId: '550e8400-e29b-41d4-a716-446655440002',
        decision: ReportReviewDecisionEnum::REJECTED,
        comment: 'Complementar a documentação.',
    );

    expect($approval->id()->value())->toBeUuid()->not->toBe($rejection->id()->value());
    expect($approval->reportId()->value())->toBe($reportId);
    expect($rejection->reportId()->value())->toBe($reportId);
    expect($approval->reviewerId()->value())->toBe('550e8400-e29b-41d4-a716-446655440001');
    expect($rejection->reviewerId()->value())->toBe('550e8400-e29b-41d4-a716-446655440002');
    expect($approval->decision())->toBe(ReportReviewDecisionEnum::APPROVED);
    expect($rejection->decision())->toBe(ReportReviewDecisionEnum::REJECTED);
    expect($approval->comment())->toBe('Documento conferido.');
    expect($rejection->comment())->toBe('Complementar a documentação.');
});

test('accepts a human decision without a comment and defaults review time to creation time', function (): void {
    $review = new ReportReviewEntity(
        reportId: '550e8400-e29b-41d4-a716-446655440010',
        reviewerId: '550e8400-e29b-41d4-a716-446655440001',
        decision: ReportReviewDecisionEnum::APPROVED,
        id: '550e8400-e29b-41d4-a716-446655440020',
        createdAt: '2020-01-01 08:00:00',
    );

    expect($review->id()->value())->toBe('550e8400-e29b-41d4-a716-446655440020');
    expect($review->comment())->toBeNull();
    expect($review->reviewedAt())->toBe($review->createdAt());
    expect($review->createdAt()->format('Y-m-d H:i:s'))->toBe('2020-01-01 08:00:00');
});

test('copies mutable review dates to preserve the recorded decision', function (): void {
    $createdAt = new DateTime('2020-01-01 08:00:00');
    $reviewedAt = new DateTime('2020-01-02 09:00:00');
    $review = new ReportReviewEntity(
        reportId: '550e8400-e29b-41d4-a716-446655440010',
        reviewerId: '550e8400-e29b-41d4-a716-446655440001',
        decision: ReportReviewDecisionEnum::APPROVED,
        createdAt: $createdAt,
        reviewedAt: $reviewedAt,
    );

    $createdAt->modify('+1 year');
    $reviewedAt->modify('+1 year');

    expect($review->createdAt())->toBeInstanceOf(DateTimeImmutable::class);
    expect($review->createdAt()->format('Y-m-d H:i:s'))->toBe('2020-01-01 08:00:00');
    expect($review->reviewedAt())->toBeInstanceOf(DateTimeImmutable::class);
    expect($review->reviewedAt()->format('Y-m-d H:i:s'))->toBe('2020-01-02 09:00:00');
});

test('rejects malformed identities in human reviews using the Report exception code', function (string $parameter): void {
    $arguments = [
        'reportId' => '550e8400-e29b-41d4-a716-446655440010',
        'reviewerId' => '550e8400-e29b-41d4-a716-446655440001',
        'decision' => ReportReviewDecisionEnum::APPROVED,
        $parameter => 'invalid-uuid',
    ];

    expect(fn () => new ReportReviewEntity(...$arguments))->toThrow(function (InvalidReportIdException $exception): void {
        expect($exception->getCode())->toBe(2001);
    });
})->with(['id', 'reportId', 'reviewerId']);

test('rejects malformed dates in human reviews using the Report date exception code', function (string $parameter): void {
    $arguments = [
        'reportId' => '550e8400-e29b-41d4-a716-446655440010',
        'reviewerId' => '550e8400-e29b-41d4-a716-446655440001',
        'decision' => ReportReviewDecisionEnum::APPROVED,
        $parameter => 'not-a-date',
    ];

    expect(fn () => new ReportReviewEntity(...$arguments))->toThrow(function (InvalidReportDateException $exception): void {
        expect($exception->getCode())->toBe(2020);
    });
})->with(['createdAt', 'reviewedAt']);
