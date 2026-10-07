<?php

declare(strict_types=1);

namespace src\Modules\Identity\Presentation\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use src\Modules\Identity\Application\Exception\InvalidCredentialsException;
use src\Modules\Identity\Application\Interfaces\Adapter\LogoutUserDataAdapterInterface;
use src\Modules\Identity\Application\Interfaces\Usecase\Auth\LogoutUserUsecaseInterface;
use src\Modules\Identity\Presentation\Http\Requests\Auth\LogoutUserRequest;
use src\Modules\Shared\Helper\ResponseJsend;
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
