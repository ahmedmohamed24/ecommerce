<?php

namespace App\Listeners;

use App\Events\VendorAddedPhoneEvent;
use App\Http\Services\SmsService;

class VendorSendSMSListener
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
    public function handle(VendorAddedPhoneEvent $event)
    {
        $sms = new SmsService();
        $sms->send($event->phone, $event->otp);
    }
}
