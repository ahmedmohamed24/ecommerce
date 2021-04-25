<?php

namespace App\Listeners;

use App\Events\UserAttachPhoneEvent;
use App\Http\Services\SmsService;

class SendSmsToUserListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     */
    public function handle(UserAttachPhoneEvent $event)
    {
        $sms = new SmsService();
        $sms->send($event->phone, $event->otp);
    }
}
