<?php

namespace App\Http\Controllers\V1\User\Auth;

use App\Http\Controllers\Controller;
use App\Http\Traits\AuthTrait;

class UserAuthController extends Controller
{
    use AuthTrait;

    public function __construct()
    {
        $this->model = 'App\Models\User';
        $this->guard = 'api';
        $this->tableName = 'users';
    }
}
