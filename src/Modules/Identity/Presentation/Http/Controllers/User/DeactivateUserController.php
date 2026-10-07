<?php

declare(strict_types=1);

namespace src\Modules\Identity\Presentation\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use src\Modules\Identity\Application\DTO\User\DeactivateUserOutputDTO;
use src\Modules\Identity\Application\Exception\UserNotFoundException;
use src\Modules\Identity\Application\Interfaces\Adapter\DeactivateUserDataAdapterInterface;
use src\Modules\Identity\Application\Interfaces\Usecase\User\DeactivateUserUsecaseInterface;
use src\Modules\Identity\Domain\Exception\InvalidUserIdException;
use src\Modules\Identity\Presentation\Http\Requests\User\DeactivateUserRequest;
use src\Modules\Shared\Helper\ResponseJsend;
use Throwable;

class DeactivateUserController extends Controller
{
    public function __construct(
        private readonly DeactivateUserUsecaseInterface $usecase,
        private readonly DeactivateUserDataAdapterInterface $adapter,
    ) {}

    public function __invoke(DeactivateUserRequest $request): JsonResponse
    {
        try {
            $input = $this->adapter->fromArray($request->validated());

            $result = DB::transaction(fn (): DeactivateUserOutputDTO => $this->usecase->__invoke($input));

            $response = new ResponseJsend($this->adapter->toArray($result));

            return $response->toJsonResponse();
        } catch (UserNotFoundException $e) {
            $response = new ResponseJsend(
                status: 'error',
                message: $e->getMessage(),
                code: $e->getCode(),
            );

            return $response->toJsonResponse(404);
        } catch (InvalidUserIdException $e) {
            $response = new ResponseJsend(
                status: 'error',
                message: $e->getMessage(),
                code: $e->getCode(),
            );

            return $response->toJsonResponse(422);
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
