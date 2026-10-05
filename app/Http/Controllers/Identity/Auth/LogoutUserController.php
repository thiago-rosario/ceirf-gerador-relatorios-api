<?php

declare(strict_types=1);

namespace App\Http\Controllers\Identity\Auth;

use App\Http\Controllers\Controller;
use App\Http\Helper\ResponseJsend;
use App\Http\Request\Identity\Auth\LogoutUserRequest;
use Illuminate\Http\JsonResponse;
use src\Identity\Application\Exception\InvalidCredentialsException;
use src\Identity\Application\Interfaces\Adapter\LogoutUserDataAdapterInterface;
use src\Identity\Application\Interfaces\Usecase\Auth\LogoutUserUsecaseInterface;
use Throwable;

class LogoutUserController extends Controller
{
    public function __construct(
        private readonly LogoutUserUsecaseInterface $usecase,
        private readonly LogoutUserDataAdapterInterface $adapter,
    ) {}

    public function __invoke(LogoutUserRequest $request): JsonResponse
    {
        try {
            $input = $this->adapter->fromArray($request->validated());

            $this->usecase->__invoke($input);

            return (new ResponseJsend)->toJsonResponse();
        } catch (InvalidCredentialsException $exception) {
            return (new ResponseJsend(
                status: ResponseJsend::STATUS_ERROR,
                message: $exception->getMessage(),
                code: $exception->getCode(),
            ))->toJsonResponse(401);
        } catch (Throwable $exception) {
            report($exception);

            return (new ResponseJsend(
                status: ResponseJsend::STATUS_ERROR,
                message: 'An unexpected error occurred',
                code: 500,
            ))->toJsonResponse(500);
        }
    }
}
