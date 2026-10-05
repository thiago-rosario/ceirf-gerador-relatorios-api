<?php

declare(strict_types=1);

namespace App\Http\Controllers\Identity\Auth;

use App\Http\Controllers\Controller;
use App\Http\Helper\ResponseJsend;
use App\Http\Request\Identity\Auth\ResetUserPasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use src\Identity\Application\DTO\Auth\ResetUserPasswordOutputDTO;
use src\Identity\Application\Exception\UserNotFoundException;
use src\Identity\Application\Interfaces\Adapter\ResetUserPasswordDataAdapterInterface;
use src\Identity\Application\Interfaces\Usecase\Auth\ResetUserPasswordUsecaseInterface;
use Throwable;

class ResetUserPasswordController extends Controller
{
    public function __construct(
        private readonly ResetUserPasswordUsecaseInterface $usecase,
        private readonly ResetUserPasswordDataAdapterInterface $adapter,
    ) {}

    public function __invoke(ResetUserPasswordRequest $request): JsonResponse
    {
        try {
            $input = $this->adapter->fromArray($request->validated());

            $result = DB::transaction(fn (): ResetUserPasswordOutputDTO => $this->usecase->__invoke($input));

            return (new ResponseJsend($this->adapter->toArray($result)))->toJsonResponse();
        } catch (UserNotFoundException $exception) {
            return (new ResponseJsend(
                status: ResponseJsend::STATUS_ERROR,
                message: $exception->getMessage(),
                code: $exception->getCode(),
            ))->toJsonResponse(404);
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
