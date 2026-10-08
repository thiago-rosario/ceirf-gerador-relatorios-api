<?php

declare(strict_types=1);

namespace src\Modules\Report\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use src\Modules\Report\Application\DTO\FindByIdReportInputDTO;
use src\Modules\Report\Application\Interfaces\Usecase\FindByIdReportUsecaseInterface;
use src\Modules\Report\Presentation\Http\Requests\ReportIdRequest;
use src\Modules\Shared\Helper\ResponseJsend;
use Symfony\Component\HttpFoundation\StreamedResponse;

use src\Modules\Report\Application\Exception\ReportNotFoundException;
use Throwable;

class DownloadReportPdfController extends Controller
{
    public function __construct(private readonly FindByIdReportUsecaseInterface $usecase) {}

    public function __invoke(ReportIdRequest $request): JsonResponse|StreamedResponse
    {
        try {
            $data = $request->validated();
            $report = $this->usecase->__invoke(new FindByIdReportInputDTO($data['id']))->report;
            $document = $report->generatedDocument;
            if ($document === null) {
                return (new ResponseJsend(
                    status: ResponseJsend::STATUS_ERROR,
                    message: 'O PDF deste relatório ainda não foi gerado.',
                    code: 409,
                ))->toJsonResponse(409);
            }
            $prefix = 'reports/'.$report->id.'/documents/';
            $path = $document->storageIdentifier;
            if (! str_starts_with($path, $prefix)
                || ! preg_match('/^[a-f0-9-]{36}\\.pdf$/i', substr($path, strlen($prefix)))
                || ! Storage::disk('local')->exists($path)) {
                return (new ResponseJsend(
                    status: ResponseJsend::STATUS_ERROR,
                    message: 'O arquivo PDF do relatório não foi encontrado.',
                    code: 404,
                ))->toJsonResponse(404);
            }
            return Storage::disk('local')->download($path, $document->fileName, ['Content-Type' => 'application/pdf']);
        } catch (ReportNotFoundException $e) {
            $response = new ResponseJsend(
                status: ResponseJsend::STATUS_ERROR,
                message: $e->getMessage(),
                code: $e->getCode(),
            );

            return $response->toJsonResponse(404);
        } catch (Throwable $e) {
            report($e);

            $response = new ResponseJsend(
                status: ResponseJsend::STATUS_ERROR,
                message: 'An unexpected error occurred',
                code: 500,
            );

            return $response->toJsonResponse(500);
        }
    }
}
