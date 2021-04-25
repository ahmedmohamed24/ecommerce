<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\PhoneRequest;

class PhoneController extends Controller
{
    public function store(PhoneRequest $request)
    {
        \auth('vendor')->user()->update([
            'phone' => $request->phone,
        ]);
    }
}
