<?php

declare(strict_types=1);

namespace src\Modules\Identity\Presentation\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use src\Modules\Identity\Application\DTO\User\UpdateUserOutputDTO;
use src\Modules\Identity\Application\Exception\UserNotFoundException;
use src\Modules\Identity\Application\Interfaces\Adapter\UpdateUserDataAdapterInterface;
use src\Modules\Identity\Application\Interfaces\Usecase\User\UpdateUserUsecaseInterface;
use src\Modules\Identity\Domain\Exception\InvalidEmailException;
use src\Modules\Identity\Domain\Exception\InvalidUserIdException;
use src\Modules\Identity\Domain\Exception\UserNameCannotBeEmptyException;
use src\Modules\Identity\Domain\Exception\UserPasswordCannotBeEmptyException;
use src\Modules\Identity\Presentation\Http\Requests\User\UpdateUserRequest;
use src\Modules\Shared\Helper\ResponseJsend;
use Throwable;

class UpdateUserController extends Controller
{
    public function __construct(
        private readonly UpdateUserUsecaseInterface $usecase,
        private readonly UpdateUserDataAdapterInterface $adapter,
    ) {}

    public function __invoke(UpdateUserRequest $request): JsonResponse
    {
        try {
            $input = $this->adapter->fromArray($request->validated());

            $result = DB::transaction(fn (): UpdateUserOutputDTO => $this->usecase->__invoke($input));

            $response = new ResponseJsend($this->adapter->toArray($result));

            return $response->toJsonResponse();
        } catch (UserNotFoundException $e) {
            $response = new ResponseJsend(
                status: 'error',
                message: $e->getMessage(),
                code: $e->getCode(),
            );

            return $response->toJsonResponse(404);
        } catch (InvalidEmailException|InvalidUserIdException|UserNameCannotBeEmptyException|UserPasswordCannotBeEmptyException $e) {
            $response = new ResponseJsend(
                status: 'error',
                message: $e->getMessage(),
                code: $e->getCode(),
            );

            return $response->toJsonResponse(422);
        } catch (UniqueConstraintViolationException $e) {
            $response = new ResponseJsend(
                status: 'error',
                message: 'O e-mail já está em uso.',
                code: 422,
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
