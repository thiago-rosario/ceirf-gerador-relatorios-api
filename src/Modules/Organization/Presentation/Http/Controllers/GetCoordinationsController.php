<?php

declare(strict_types=1);

namespace src\Modules\Organization\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use src\Modules\Organization\Application\Interfaces\Adapter\GetCoordinationsDataAdapterInterface;
use src\Modules\Organization\Application\Interfaces\Usecase\GetCoordinationsUsecaseInterface;
use src\Modules\Shared\Helper\ResponseJsend;
use Throwable;

class GetCoordinationsController extends Controller
{
    public function __construct(
        private readonly GetCoordinationsUsecaseInterface $usecase,
        private readonly GetCoordinationsDataAdapterInterface $adapter,
    ) {}

    public function __invoke(): JsonResponse
    {
        try {
            $result = $this->usecase->__invoke();

            $response = new ResponseJsend($this->adapter->toArray($result));

            return response()
                ->json($response->toArray(), 200);
        } catch (Throwable $e) {
            report($e);

            $response = new ResponseJsend(
                status: 'error',
                message: 'An unexpected error occurred',
                code: 500,
            );

            return response()
                ->json($response->toArray(), 500);
        }
    }
}
