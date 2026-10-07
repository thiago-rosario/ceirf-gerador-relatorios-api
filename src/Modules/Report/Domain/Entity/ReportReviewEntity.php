<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Entity;

use DateTimeImmutable;
use DateTimeInterface;
use src\Modules\Report\Domain\Enum\ReportReviewDecisionEnum;
use src\Modules\Report\Domain\Trait\ResolvesReportValuesTrait;
use src\Modules\Shared\Resolver\UuidResolver;

/**
 * Decisão individual de revisão humana. Várias decisões podem referir-se à mesma versão.
 */
class ReportReviewEntity
{
    use ResolvesReportValuesTrait;

    private UuidResolver $id;

    private UuidResolver $reportId;

    private UuidResolver $reviewerId;

    private DateTimeImmutable $createdAt;

    private DateTimeImmutable $reviewedAt;

    public function __construct(
        UuidResolver|string $reportId,
        UuidResolver|string $reviewerId,
        private ReportReviewDecisionEnum $decision,
        private ?string $comment = null,
        UuidResolver|string|null $id = null,
        DateTimeInterface|string|null $createdAt = null,
        DateTimeInterface|string|null $reviewedAt = null,
    ) {
        $this->id = $this->resolveUuid($id);
        $this->reportId = $this->resolveUuid($reportId);
        $this->reviewerId = $this->resolveUuid($reviewerId);
        $this->createdAt = $this->resolveDateTime($createdAt);
        $this->reviewedAt = $this->resolveDateTime($reviewedAt, $this->createdAt);
    }

    public function id(): UuidResolver
    {
        return $this->id;
    }

    public function reportId(): UuidResolver
    {
        return $this->reportId;
    }

    public function reviewerId(): UuidResolver
    {
        return $this->reviewerId;
    }

    public function decision(): ReportReviewDecisionEnum
    {
        return $this->decision;
    }

    public function comment(): ?string
    {
        return $this->comment;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function reviewedAt(): DateTimeImmutable
    {
        return $this->reviewedAt;
    }
}
