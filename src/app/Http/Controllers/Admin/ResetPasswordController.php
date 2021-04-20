<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Traits\JsonResponse;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Auth\ResetsPasswords;

class ResetPasswordController extends Controller
{
    use ResetsPasswords,JsonResponse;
    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;
    /**
         * Get the broker to be used during password reset.
         *
         * @return \Illuminate\Contracts\Auth\PasswordBroker
         */
    public function broker()
    {
        return Password::broker('admins');
    }
    /**
     * this is to get data instead of showResetForm in Trait
     *
     * @param Request $request
     * @return void
     */
    public function getResetFormData(Request $request)
    {
        $token = $request->route()->parameter('token');
        return $this->response(200, 'success', ['token' => $token, 'email' => $request->email]);
    }
}
