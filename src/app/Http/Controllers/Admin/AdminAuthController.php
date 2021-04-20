<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\JsonResponse;
use App\Models\Admin;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminAuthController extends Controller
{
    use JsonResponse;

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
        if ($token = Auth::guard('admin')->attempt($request->only(['email', 'password']))) {
            return $this->response('success', 200, ['access_token' => $token, 'token_type' => 'bearer', 'expires_in' => \auth()->guard('admin')->factory()->getTTL() * 60]);
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
            $request->only(['name', 'email', 'password', 'password_confirm']),
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'unique:admins,email'],
                'password' => ['required_with:password_confirm', 'same:password_confirm', 'min:6'],
            ]
        );
        if ($validator->fails()) {
            return $this->response('error', 400, $validator->getMessageBag());
        }

        try {
            DB::beginTransaction();
            Admin::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            $token = auth('admin')->attempt($request->only('email', 'password'));
            DB::commit();

            return $this->response('success', 200, ['access_token' => $token, 'token_type' => 'bearer', 'expires_in' => \auth()->guard('admin')->factory()->getTTL() * 60]);
        } catch (\Exception $e) {
            //send getMessage() to log file and return message of error
            DB::rollback();

            return $this->response('error', 401, $e->getMessage());
        }
    }

    /**
     *  logout the authenticated user.
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        return $this->response('success', 200, null);
    }

    public function getAuthUser()
    {
        return $this->response('success', 200, Auth::guard('admin')->user());
    }
}
