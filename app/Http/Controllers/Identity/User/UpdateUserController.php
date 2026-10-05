<?php

declare(strict_types=1);

namespace App\Http\Controllers\Identity\User;

use App\Http\Controllers\Controller;
use App\Http\Helper\ResponseJsend;
use App\Http\Request\Identity\User\UpdateUserRequest;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use src\Identity\Application\DTO\User\UpdateUserOutputDTO;
use src\Identity\Application\Exception\UserNotFoundException;
use src\Identity\Application\Interfaces\Adapter\UpdateUserDataAdapterInterface;
use src\Identity\Application\Interfaces\Usecase\User\UpdateUserUsecaseInterface;
use src\Identity\Domain\Exception\InvalidEmailException;
use src\Identity\Domain\Exception\InvalidUserIdException;
use src\Identity\Domain\Exception\UserNameCannotBeEmptyException;
use src\Identity\Domain\Exception\UserPasswordCannotBeEmptyException;
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
