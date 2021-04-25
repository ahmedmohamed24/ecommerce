<?php

namespace App\Listeners;

use App\Events\VendorAddedPhoneEvent;
use App\Http\Services\TwillioService;

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
        $sms = new TwillioService();
        $sms->send($event->phone, $event->otp);
    }
}
