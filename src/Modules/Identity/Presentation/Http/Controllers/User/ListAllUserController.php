<?php

declare(strict_types=1);

namespace src\Modules\Identity\Presentation\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use src\Modules\Identity\Application\Interfaces\Adapter\ListAllUserDataAdapterInterface;
use src\Modules\Identity\Application\Interfaces\Usecase\User\ListAllUserUsecaseInterface;
use src\Modules\Identity\Presentation\Http\Requests\User\ListAllUserRequest;
use src\Modules\Shared\Helper\ResponseJsend;
use Throwable;

class ListAllUserController extends Controller
{
    public function __construct(
        private readonly ListAllUserUsecaseInterface $usecase,
        private readonly ListAllUserDataAdapterInterface $adapter,
    ) {}

    public function __invoke(ListAllUserRequest $request): JsonResponse
    {
        try {
            $input = $this->adapter->fromArray($request->validated());

            $result = $this->usecase->__invoke($input);

            $response = new ResponseJsend($this->adapter->toArray($result));

            return $response->toJsonResponse();
        } catch (Throwable $e) {
            report($e);

            $response = new ResponseJsend(
                status: 'error',
                message: 'An unexpected error occurred',
                code: 500,
            );

            return $response->toJsonResponse(500);
        }
    }
}
