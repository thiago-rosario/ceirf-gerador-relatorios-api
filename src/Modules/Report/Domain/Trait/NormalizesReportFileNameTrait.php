<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Trait;

trait NormalizesReportFileNameTrait
{
    /**
     * Preserva acentos e neutraliza separadores e caracteres reservados de nomes de arquivo.
     */
    private static function normalizeFileNamePart(string $value): string
    {
        return trim(preg_replace('/[\s\p{C}<>:"\/\\\\|?*._]+/u', '_', mb_strtoupper(trim($value))) ?? '', '_');
    }
}
