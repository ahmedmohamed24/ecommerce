<?php

namespace App\Http\Interfaces;

use Illuminate\Http\Request;

interface Payment
{
    public function createOrder(Request $request);

    public static function setCredentials();

    public function insertIntoDB($data);

    public function logError(string $message);
}
