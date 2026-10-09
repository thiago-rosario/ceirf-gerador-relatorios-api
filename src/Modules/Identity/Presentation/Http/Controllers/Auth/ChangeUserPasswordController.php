<?php

declare(strict_types=1);

namespace src\Modules\Identity\Presentation\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use src\Modules\Identity\Application\DTO\Auth\ChangeUserPasswordInputDTO;
use src\Modules\Identity\Application\DTO\User\FindByIdUserOutputDTO;
use src\Modules\Identity\Application\Exception\PasswordChangeRejectedException;
use src\Modules\Identity\Application\Exception\UserNotFoundException;
use src\Modules\Identity\Application\Interfaces\Adapter\FindByIdUserDataAdapterInterface;
use src\Modules\Identity\Application\Interfaces\Usecase\Auth\ChangeUserPasswordUsecaseInterface;
use src\Modules\Identity\Model\User;
use src\Modules\Identity\Presentation\Http\Requests\Auth\ChangeUserPasswordRequest;
use src\Modules\Shared\Helper\ResponseJsend;
use Throwable;

class ChangeUserPasswordController extends Controller
{
    public function __construct(
        private readonly ChangeUserPasswordUsecaseInterface $usecase,
        private readonly FindByIdUserDataAdapterInterface $adapter,
    ) {}

    public function __invoke(ChangeUserPasswordRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $data = $request->validated();

        try {
            $input = new ChangeUserPasswordInputDTO(
                id: $user->uuid,
                currentPassword: $data['current_password'],
                password: $data['password'],
                accessToken: $request->bearerToken() ?? '',
            );

            $result = DB::transaction(fn (): FindByIdUserOutputDTO => ($this->usecase)($input));

            return (new ResponseJsend(['user' => $this->adapter->toArray($result)]))->toJsonResponse();
        } catch (PasswordChangeRejectedException $exception) {
            $response = new ResponseJsend(
                status: ResponseJsend::STATUS_ERROR,
                message: $exception->getMessage(),
                code: 422,
            );

            return response()->json($response->toArray() + ['errors' => [$exception->field => [$exception->getMessage()]]], 422);
        } catch (AuthenticationException|UserNotFoundException $exception) {
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
