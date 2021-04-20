<?php

namespace App\Http\Middleware;

use App\Http\Traits\JsonResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    use JsonResponse;

    /**
     * Handle an incoming request.
     *
     * @param null|string ...$guards
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return $this->response('already authenticated', 302, []);
            }
        }

        return $next($request);
    }
}
