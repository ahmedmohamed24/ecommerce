<?php

namespace App\Http\Controllers\User\Auth;

use Illuminate\Http\Request;
use App\Http\Traits\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth as AuthGuard;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Contracts\Providers\JWT;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
    use JsonResponse;
    public function guard()
    {
        return AuthGuard::guard('api');
    }
    public function login(Request $request)
    {
        $validator=Validator::make(
            $request->only('email', 'password'),
            [
                'email'=>'required|email|max:255',
                'password'=>'required|min:8'
            ]
        );
        if ($validator->fails()) {
            return $this->response('fail', 401, $validator->getMessageBag());
        }
        if ($token = $this->guard()->attempt($request->only('email', 'password'))) {
            return $this->response(
                'sucess',
                200,
                ['access_token' => $token, 'token_type' => 'bearer', 'expires_in' =>$this->guard()->factory()->getTTL() * 60]
            );
        }
        return $this->response('These credentials does not match our records', 403);
    }
}
