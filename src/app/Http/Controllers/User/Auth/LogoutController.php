<?php

namespace App\Http\Controllers\User\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Traits\JsonResponse;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    use JsonResponse;
    public function logout(Request $request)
    {
        Auth::guard('api')->logout();
        return $this->response('success', 200);
    }
}
