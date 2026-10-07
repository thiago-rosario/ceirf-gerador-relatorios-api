<?php

declare(strict_types=1);

namespace src\Modules\Identity\Presentation\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use src\Modules\Identity\Application\DTO\Auth\ResetUserPasswordOutputDTO;
use src\Modules\Identity\Application\Exception\UserNotFoundException;
use src\Modules\Identity\Application\Interfaces\Adapter\ResetUserPasswordDataAdapterInterface;
use src\Modules\Identity\Application\Interfaces\Usecase\Auth\ResetUserPasswordUsecaseInterface;
use src\Modules\Identity\Presentation\Http\Requests\Auth\ResetUserPasswordRequest;
use src\Modules\Shared\Helper\ResponseJsend;
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
