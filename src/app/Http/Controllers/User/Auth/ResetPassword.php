<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResetPasswordTrait;

class ResetPassword extends Controller
{
    use ResetPasswordTrait;

    public function __construct()
    {
        $this->tableName = 'users';
        $this->model = 'App\Models\User';
        $this->guard = 'api';
    }
}
