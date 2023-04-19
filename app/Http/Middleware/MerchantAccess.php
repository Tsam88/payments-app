<?php

namespace App\Http\Middleware;

use App\Models\UserRole;
use App\Services\UserService;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;

class MerchantAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  UserService $userService
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next, UserService $userService)
    {
        $user = $request->user();

        $userRole = $userService->getUserRole($user);

        if ($userRole !== UserRole::MERCHANT) {
            throw new AuthorizationException();
        }

        return $next($request);
    }
}
