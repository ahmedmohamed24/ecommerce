<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResetPasswordTrait;

class ResetPasswordController extends Controller
{
    use ResetPasswordTrait;

    public function __construct()
    {
        $this->model = 'App\Models\Admin';
        $this->tableName = 'admins';
        $this->guard = 'admin';
    }
}
