<?php

namespace App\Http\Interfaces;

interface Payment
{
    public function createOrder($data, $token = null);

    public static function setCredentials();

    public function insertIntoDB($data);

    public function logError(string $message);
}
