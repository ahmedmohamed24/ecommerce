<?php

namespace App\Http\Traits;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

trait AuthTrait
{
    use JsonResponse;
    public $model = '';
    public $guard = '';
    public $tableName = '';

    /**
     *  admin login.
     *
     * @return json
     */
    public function login(Request $request)
    {
        $validator = Validator::make(
            $request->only(['email', 'password']),
            [
                'email' => ['required', 'email', 'max:255'],
                'password' => ['required', 'string'],
            ]
        );
        if ($validator->fails()) {
            return $this->response('error', 400, $validator->getMessageBag());
        }
        if ($token = Auth::guard($this->guard)->attempt($request->only(['email', 'password']))) {
            return $this->response('success', 200, ['access_token' => $token, 'token_type' => 'bearer', 'expires_in' => \auth()->guard($this->guard)->factory()->getTTL() * 60]);
        }

        return $this->response('These credentials does not match our records', 401, null);
    }

    /**
     * register admin.
     *
     * @return json
     */
    public function register(Request $request)
    {
        $validator = Validator::make(
            $request->only(['name', 'email', 'password', 'password_confirmation']),
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', "unique:{$this->tableName},email"],
                'password' => ['required', 'min:8', 'confirmed'],
            ]
        );
        if ($validator->fails()) {
            return $this->response('error', 400, $validator->getMessageBag());
        }

        try {
            DB::beginTransaction();
            $this->model::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            $token = auth($this->guard)->attempt($request->only('email', 'password'));
            DB::commit();

            return $this->response('success', 201, ['access_token' => $token, 'token_type' => 'bearer', 'expires_in' => \auth()->guard($this->guard)->factory()->getTTL() * 60]);
        } catch (\Exception $e) {
            //send getMessage() to log file and return message of error
            DB::rollback();

            return $this->response('error', 401, $e->getMessage());
        }
    }

    /**
     *  logout the authenticated user.
     */
    public function logout()
    {
        Auth::guard($this->guard)->logout();

        return $this->response('success', 200, null);
    }

    public function getAuthUser()
    {
        return $this->response('success', 200, Auth::guard($this->guard)->user());
    }

    public function refreshToken()
    {
        return $this->response(
            'success',
            200,
            ['access_token' => Auth::guard($this->guard)->refresh(), 'token_type' => 'bearer', 'expires_in' => Auth::guard($this->guard)->factory()->getTTL() * 60]
        );
    }
}
