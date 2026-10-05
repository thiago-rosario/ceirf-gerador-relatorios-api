<?php

declare(strict_types=1);

namespace App\Http\Controllers\Identity\User;

use App\Http\Controllers\Controller;
use App\Http\Helper\ResponseJsend;
use App\Http\Request\Identity\User\ListAllUserRequest;
use Illuminate\Http\JsonResponse;
use src\Identity\Application\Interfaces\Adapter\ListAllUserDataAdapterInterface;
use src\Identity\Application\Interfaces\Usecase\User\ListAllUserUsecaseInterface;
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
