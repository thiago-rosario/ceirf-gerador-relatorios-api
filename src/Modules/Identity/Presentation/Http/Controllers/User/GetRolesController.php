<?php

declare(strict_types=1);

namespace src\Modules\Identity\Presentation\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use src\Modules\Identity\Application\Interfaces\Adapter\GetRolesDataAdapterInterface;
use src\Modules\Identity\Application\Interfaces\Usecase\User\GetRolesUsecaseInterface;
use src\Modules\Shared\Helper\ResponseJsend;

class GetRolesController extends Controller
{
    public function __construct(
        private readonly GetRolesUsecaseInterface $usecase,
        private readonly GetRolesDataAdapterInterface $adapter,
    ) {}

    public function __invoke(): JsonResponse
    {
        try {
            $result = $this->usecase->__invoke();

            $response = new ResponseJsend($this->adapter->toArray($result));

            return response()
                ->json($response->toArray(), 200);
        } catch (\Throwable $e) {
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
