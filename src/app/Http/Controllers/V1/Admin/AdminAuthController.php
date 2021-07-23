<?php

namespace App\Http\Controllers\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\AuthTrait;
use Illuminate\Http\Request;

class AdminAuthController extends Controller
{
    use AuthTrait;

    public function __construct()
    {
        $this->model = 'App\Models\Admin';
        $this->guard = 'admin';
        $this->tableName = 'admins';
    }

    public function register(Request $request)
    {
        //admin cannot register
    }
}
