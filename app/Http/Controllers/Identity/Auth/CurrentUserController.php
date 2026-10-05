<?php

declare(strict_types=1);

namespace App\Http\Controllers\Identity\Auth;

use App\Http\Controllers\Controller;
use App\Http\Helper\ResponseJsend;
use App\Model\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use src\Identity\Application\Exception\UserNotFoundException;
use src\Identity\Application\Interfaces\Adapter\FindByIdUserDataAdapterInterface;
use src\Identity\Application\Interfaces\Usecase\User\FindByIdUserUsecaseInterface;
use Throwable;

class CurrentUserController extends Controller
{
    public function __construct(
        private readonly FindByIdUserUsecaseInterface $usecase,
        private readonly FindByIdUserDataAdapterInterface $adapter,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        try {
            $input = $this->adapter->fromArray(['id' => $user->uuid]);

            $result = $this->usecase->__invoke($input);

            return (new ResponseJsend([
                'user' => $this->adapter->toArray($result),
            ]))->toJsonResponse();
        } catch (UserNotFoundException $exception) {
            return (new ResponseJsend(
                status: ResponseJsend::STATUS_ERROR,
                message: 'Não autenticado.',
                code: 401,
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
