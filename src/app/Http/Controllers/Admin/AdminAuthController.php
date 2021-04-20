<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\AuthTrait;

class AdminAuthController extends Controller
{
    use AuthTrait;

    public function __construct()
    {
        $this->model = 'App\Models\Admin';
        $this->guard = 'admin';
        $this->tableName = 'admins';
    }
}
