<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use App\Http\Traits\JsonResponse;
use Illuminate\Support\Facades\Auth as AuthGuard;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use JsonResponse;
    public function guard()
    {
        return AuthGuard::guard('api');
    }
    public function getAuthUser()
    {
        return $this->response('success', 200, $this->guard()->user());
    }
    public function refreshToken()
    {
        return $this->response(
            'success',
            200,
            ['access_token' => $this->guard()->refresh(), 'token_type' => 'bearer', 'expires_in' =>$this->guard()->factory()->getTTL() * 60]
        );
    }
}
