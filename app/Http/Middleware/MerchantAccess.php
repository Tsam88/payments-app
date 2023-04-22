<?php

namespace App\Http\Middleware;

use App\Models\UserRole;
use App\Services\UserService;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;

class MerchantAccess
{
    /**
     * @var UserService
     */
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {
        $user = $request->user();

        if ($user->userRole->name !== UserRole::MERCHANT) {
            throw new AuthorizationException();
        }

        return $next($request);
    }
}
