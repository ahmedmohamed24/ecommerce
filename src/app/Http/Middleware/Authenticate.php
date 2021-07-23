<?php

namespace App\Http\Middleware;

use App\Http\Traits\JsonResponse;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    use JsonResponse;

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return null|string
     */
    protected function redirectTo($request)
    {
        if (!$request->expectsJson()) {
            return $this->response('not authenticated', 302, []);
        }
    }
}
