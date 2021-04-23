<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Traits\AuthTrait;

class VendorAuthController extends Controller
{
    use AuthTrait;

    public function __construct()
    {
        $this->model = 'App\Models\Vendor';
        $this->guard = 'vendor';
        $this->tableName = 'vendors';
    }
}
