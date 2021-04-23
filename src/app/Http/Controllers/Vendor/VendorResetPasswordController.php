<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResetPasswordTrait;

class VendorResetPasswordController extends Controller
{
    use ResetPasswordTrait;

    public function __construct()
    {
        $this->tableName = 'vendors';
        $this->model = 'App\Models\Vendor';
        $this->guard = 'vendor';
    }
}
