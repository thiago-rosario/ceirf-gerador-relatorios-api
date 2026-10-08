<?php

declare(strict_types=1);

namespace src\Modules\Report\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use src\Modules\Report\Application\Interfaces\Usecase\CountAllReportsUsecaseInterface;
use src\Modules\Shared\Helper\ResponseJsend;

use Throwable;

class CountAllReportsController extends Controller
{
    public function __construct(
        private readonly CountAllReportsUsecaseInterface $usecase
    ){}

    public function __invoke(): JsonResponse
    {
        try {
            $result = $this->usecase->__invoke();
            $response = new ResponseJsend(['count' => $result->count]);

            return $response->toJsonResponse();
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
