<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use App\Http\Traits\JsonResponse;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    use JsonResponse;

    public function register(Request $request)
    {
        $validator = Validator::make(
            $request->only(['name', 'email', 'password', 'password_confirmation']),
            [
                'name' => ['required', 'min:2'],
                'email' => ['required', 'email', 'unique:users,email'],
                'password' => ['required', 'min:8', 'confirmed'],
            ]
        );
        if ($validator->fails()) {
            return $this->response('fail', 401, $validator->getMessageBag());
        }
        $credentials = $request->only(['name', 'email', 'password']);
        $credentials['password'] = Hash::make($credentials['password']);
        $user = User::create($credentials);
        $token = Auth::login($user);
        event(new Registered($user));

        return $this->response(
            'success',
            201,
            [
                'token_type' => 'bearer',
                'access_token' => $token,
                'expires_in' => Auth::guard()->factory()->getTTl() * 60,
            ]
        );
    }
}
