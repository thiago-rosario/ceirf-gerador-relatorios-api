<?php

declare(strict_types=1);

namespace src\Modules\Report\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use src\Modules\Report\Application\DTO\CountReportsByMonthInputDTO;
use src\Modules\Report\Application\DTO\MonthlyReportCountDataDTO;
use src\Modules\Report\Application\Interfaces\Usecase\CountReportsByMonthUsecaseInterface;
use src\Modules\Report\Presentation\Http\Requests\CountReportsByMonthRequest;
use src\Modules\Shared\Helper\ResponseJsend;

use Throwable;

class CountReportsByMonthController extends Controller
{
    public function __construct(
        private readonly CountReportsByMonthUsecaseInterface $usecase
    ){}

    public function __invoke(CountReportsByMonthRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $result = $this->usecase->__invoke(new CountReportsByMonthInputDTO($data['month']));
            return (new ResponseJsend([
                'month' => $result->month,
                'counts' => array_map(fn (MonthlyReportCountDataDTO $count): array => ['month' => $count->month, 'count' => $count->count], $result->counts),
            ]))->toJsonResponse();
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
