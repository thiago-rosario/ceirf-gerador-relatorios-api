<?php

declare(strict_types=1);

namespace src\Modules\Report\Infra\Service;

use finfo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use src\Modules\Report\Domain\Entity\ReportEntity;
use src\Modules\Report\Domain\Exception\InvalidReportFileReferenceException;
use src\Modules\Report\Domain\Validation\ReportMediaValidation;
use src\Modules\Report\Domain\ValueObject\ReportFileReferenceValueObject;
use Symfony\Component\Process\Process;

class ReportPdfMediaService
{
    /** @return array<string, list<string>> */
    public function resolve(ReportEntity $report): array
    {
        $media = [];

        foreach (ReportMediaValidation::files($report) as $file) {
            $contents = $this->contents($report, $file);
            $media[$file->storageIdentifier()] = $file->mimeType() === 'application/pdf'
                ? $this->convertPdf($contents)
                : ['data:'.$file->mimeType().';base64,'.base64_encode($contents)];
        }

        return $media;
    }

    private function contents(ReportEntity $report, ReportFileReferenceValueObject $file): string
    {
        $path = $file->storageIdentifier();
        $prefix = 'reports/'.$report->rootReportId()->value().'/media/';

        if (! str_starts_with($path, $prefix)
            || preg_match('/\A[a-zA-Z0-9_-]+\.(png|jpe?g|pdf)\z/', substr($path, strlen($prefix))) !== 1
            || ! Storage::disk('local')->exists($path)) {
            throw new InvalidReportFileReferenceException('O arquivo do relatório não foi encontrado no armazenamento da sua família.');
        }

        $contents = Storage::disk('local')->get($path);

        if ($contents === null || strlen($contents) !== $file->sizeBytes()
            || ! hash_equals($file->checksum(), hash('sha256', $contents))
            || (new finfo(FILEINFO_MIME_TYPE))->buffer($contents) !== $file->mimeType()) {
            throw new InvalidReportFileReferenceException('O conteúdo do arquivo não corresponde aos metadados informados.');
        }

        return $contents;
    }

    /** @return list<string> */
    private function convertPdf(string $contents): array
    {
        $disk = Storage::disk('local');
        $directory = 'reports/tmp/'.Str::uuid();
        $maximumPages = (int) config('report_pdf.maximum_pdf_pages', 20);

        try {
            if (! $disk->put($directory.'/source.pdf', $contents)) {
                throw new RuntimeException('Não foi possível preparar o PDF anexado.');
            }

            $process = new Process([
                (string) config('report_pdf.pdftoppm_binary', 'pdftoppm'),
                '-f', '1', '-l', (string) ($maximumPages + 1),
                '-scale-to', '1400', '-png',
                $disk->path($directory.'/source.pdf'), $disk->path($directory.'/page'),
            ]);
            $process->setTimeout(60)->mustRun();
            $paths = array_values(array_filter($disk->files($directory), fn (string $path): bool => str_ends_with($path, '.png')));
            natsort($paths);

            if ($paths === [] || count($paths) > $maximumPages) {
                throw new InvalidReportFileReferenceException('O PDF anexado deve possuir entre 1 e '.$maximumPages.' páginas.');
            }

            $images = [];

            foreach ($paths as $path) {
                $image = $disk->get($path) ?? throw new RuntimeException('Não foi possível ler a página do PDF anexado.');
                $images[] = 'data:image/png;base64,'.base64_encode($image);
            }

            return $images;
        } finally {
            $disk->deleteDirectory($directory);
        }
    }
}
