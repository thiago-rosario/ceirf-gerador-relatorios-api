<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\ValueObject;

readonly class ReportConclusionValueObject
{
    public function __construct(private string $content = '') {}

    public function content(): string
    {
        return $this->content;
    }
}
