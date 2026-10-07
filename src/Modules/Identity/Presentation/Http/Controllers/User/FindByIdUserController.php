<?php

declare(strict_types=1);

namespace src\Modules\Identity\Presentation\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use src\Modules\Identity\Application\Exception\InvalidUserSearchException;
use src\Modules\Identity\Application\Exception\UserNotFoundException;
use src\Modules\Identity\Application\Interfaces\Adapter\FindByIdUserDataAdapterInterface;
use src\Modules\Identity\Application\Interfaces\Usecase\User\FindByIdUserUsecaseInterface;
use src\Modules\Identity\Domain\Exception\InvalidEmailException;
use src\Modules\Identity\Domain\Exception\InvalidUserIdException;
use src\Modules\Identity\Domain\Exception\UserNameCannotBeEmptyException;
use src\Modules\Identity\Presentation\Http\Requests\User\FindByIdUserRequest;
use src\Modules\Shared\Helper\ResponseJsend;
use Throwable;

class FindByIdUserController extends Controller
{
    public function __construct(
        private readonly FindByIdUserUsecaseInterface $usecase,
        private readonly FindByIdUserDataAdapterInterface $adapter,
    ) {}

    public function __invoke(FindByIdUserRequest $request): JsonResponse
    {
        try {
            $input = $this->adapter->fromArray($request->validated());

            $result = $this->usecase->__invoke($input);

            $response = new ResponseJsend($this->adapter->toArray($result));

            return $response->toJsonResponse();
        } catch (UserNotFoundException $e) {
            $response = new ResponseJsend(
                status: 'error',
                message: $e->getMessage(),
                code: $e->getCode(),
            );

            return $response->toJsonResponse(404);
        } catch (InvalidUserSearchException|InvalidEmailException|InvalidUserIdException|UserNameCannotBeEmptyException $e) {
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
