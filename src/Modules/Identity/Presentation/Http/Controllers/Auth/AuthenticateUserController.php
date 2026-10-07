<?php

declare(strict_types=1);

namespace src\Modules\Identity\Presentation\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use src\Modules\Identity\Application\Exception\InvalidCredentialsException;
use src\Modules\Identity\Application\Interfaces\Adapter\AuthenticateUserAdapterInterface;
use src\Modules\Identity\Application\Interfaces\Usecase\Auth\AuthenticateUserUsecaseInterface;
use src\Modules\Identity\Presentation\Exception\AuthenticateUserException;
use src\Modules\Identity\Presentation\Http\Requests\Auth\AuthenticateUserRequest;
use src\Modules\Shared\Helper\ResponseJsend;

class AuthenticateUserController extends Controller
{
    public function __construct(
        private readonly AuthenticateUserUsecaseInterface $usecase,
        private readonly AuthenticateUserAdapterInterface $adapter,
    ) {}

    public function __invoke(AuthenticateUserRequest $request): JsonResponse
    {
        try {
            $input = $this->adapter->fromArray($request->validated());

            $result = $this->usecase->__invoke($input);

            $response = new ResponseJsend($this->adapter->toArray($result));

            return response()
                ->json($response->toArray(), 200);
        } catch (AuthenticateUserException|InvalidCredentialsException $e) {
            $response = new ResponseJsend(
                status: 'error',
                message: $e->getMessage(),
                code: $e->getCode(),
            );

            return response()
                ->json($response->toArray(), 401);
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
