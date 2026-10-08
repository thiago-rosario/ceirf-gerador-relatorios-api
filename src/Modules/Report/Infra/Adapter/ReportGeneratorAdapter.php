<?php

declare(strict_types=1);

namespace src\Modules\Report\Infra\Adapter;

use Barryvdh\DomPDF\Facade\Pdf;
use DOMElement;
use Dompdf\Canvas;
use Dompdf\FontMetrics;
use Dompdf\Frame;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use src\Modules\Report\Application\Interfaces\Adapter\ReportGeneratorAdapterInterface;
use src\Modules\Report\Application\Interfaces\Service\ReportDataMapperServiceInterface;
use src\Modules\Report\Domain\Entity\ReportEntity;
use src\Modules\Report\Domain\ValueObject\GeneratedReportValueObject;
use src\Modules\Report\Infra\Service\ReportPdfMediaService;
use src\Modules\Report\Presentation\Resources\ReportPdfResource;

class ReportGeneratorAdapter implements ReportGeneratorAdapterInterface
{
    public function __construct(
        private readonly ReportDataMapperServiceInterface $mapper,
        private readonly ReportPdfMediaService $media,
    ) {}

    public function generate(ReportEntity $report): GeneratedReportValueObject
    {
        $report->validateForGeneration();
        $resource = new ReportPdfResource($this->mapper->map($report), $this->media->resolve($report));
        $pages = [];
        $output = null;
        $fontDirectories = [];

        foreach (['arial_regular', 'arial_bold'] as $fontOption) {
            $fontPath = config('report_pdf.'.$fontOption);

            if (is_string($fontPath) && is_file($fontPath)) {
                $fontDirectories[] = dirname($fontPath);
            }
        }

        if ($fontDirectories !== []) {
            foreach (['font_dir', 'font_cache'] as $directoryOption) {
                File::ensureDirectoryExists((string) config('dompdf.options.'.$directoryOption, storage_path('fonts')));
            }
        }

        for ($pass = 0; $pass < 4; $pass++) {
            $actualPages = [];
            $pdf = Pdf::loadHTML($resource->render($pages))
                ->setPaper('a4', 'portrait')
                ->setOption([
                    'isRemoteEnabled' => false,
                    'isPhpEnabled' => false,
                    'isJavascriptEnabled' => false,
                    'defaultFont' => 'DejaVu Sans',
                    'chroot' => [base_path(), ...$fontDirectories],
                ]);
            $pdf->getDomPDF()->setCallbacks([
                ['event' => 'begin_frame', 'f' => function (Frame $frame, Canvas $canvas) use (&$actualPages): void {
                    $node = $frame->get_node();

                    if ($node instanceof DOMElement && $node->hasAttribute('data-pdf-anchor')) {
                        $anchor = $node->getAttribute('data-pdf-anchor');
                        $actualPages[$anchor] ??= $canvas->get_page_number() - 1;
                    }
                }],
                ['event' => 'end_document', 'f' => function (int $pageNumber, int $pageCount, Canvas $canvas, FontMetrics $fontMetrics): void {
                    if ($pageNumber > 1) {
                        $font = $fontMetrics->getFont('Arial') ?? $fontMetrics->getFont('DejaVu Sans');
                        $text = (string) ($pageNumber - 1);
                        $width = $fontMetrics->getTextWidth($text, $font, 10);
                        $canvas->text($canvas->get_width() - 56.69 - $width, $canvas->get_height() - 40, $text, $font, 10);
                    }
                }],
            ]);
            $output = $pdf->output();

            if ($actualPages === $pages) {
                break;
            }

            $pages = $actualPages;
            $output = null;
        }

        if ($output === null) {
            throw new RuntimeException('Não foi possível estabilizar a paginação do relatório.');
        }

        $path = 'reports/'.$report->id()->value().'/documents/'.Str::uuid().'.pdf';

        if (! Storage::disk('local')->put($path, $output)) {
            throw new RuntimeException('Não foi possível armazenar o PDF do relatório.');
        }

        return new GeneratedReportValueObject($path, $report->fileName(), now()->toDateTimeImmutable());
    }
}
