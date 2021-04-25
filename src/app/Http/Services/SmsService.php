<?php

namespace App\Http\Services;

use Twilio\Rest\Client;

class SmsService
{
    public $client;

    public function __construct()
    {
        $sid = \env('Twilio_ACCOUNT_SID');
        $token = \env('Twilio_AUTH_TOKEN');
        $this->client = new Client($sid, $token);
    }

    public function send(string $phone, int $otp)
    {
        return $this->client->messages->create(
            // '+201212924690',
            $phone,
            [
                'from' => '+16035132115',
                'body' => 'Your verification number is '.$otp,
            ]
        );
    }
}
