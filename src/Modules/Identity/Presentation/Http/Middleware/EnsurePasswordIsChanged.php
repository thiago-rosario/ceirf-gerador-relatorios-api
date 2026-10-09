<?php

declare(strict_types=1);

namespace src\Modules\Identity\Presentation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use src\Modules\Identity\Model\User;
use src\Modules\Shared\Helper\ResponseJsend;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof User && $user->must_change_password) {
            if (! $request->routeIs('auth.logout', 'auth.change-password')) {
                return (new ResponseJsend(
                    data: ['must_change_password' => true],
                    status: ResponseJsend::STATUS_ERROR,
                    message: 'É necessário alterar a senha antes de continuar.',
                    code: 403,
                ))->toJsonResponse(403);
            }
        }

        return $next($request);
    }
}
