<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Validation;

final class ReportTextValidation
{
    public static function isBlank(string $text): bool
    {
        return trim($text) === '' || preg_match('/^\s+$/u', $text) === 1;
    }
}
