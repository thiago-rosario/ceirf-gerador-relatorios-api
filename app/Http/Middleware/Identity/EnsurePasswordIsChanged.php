<?php

declare(strict_types=1);

namespace App\Http\Middleware\Identity;

use App\Http\Helper\ResponseJsend;
use App\Model\User;
use Closure;
use Illuminate\Http\Request;
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
            $isPasswordChange = $request->routeIs('users.update')
                && $request->route('id') === $user->uuid
                && $request->filled('password')
                && $request->except(['password', 'password_confirmation']) === [];

            if (! $request->routeIs('auth.logout') && ! $isPasswordChange) {
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
