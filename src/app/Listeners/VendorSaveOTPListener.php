<?php

namespace App\Listeners;

use App\Events\VendorAddedPhoneEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class VendorSaveOTPListener
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
        DB::table('sms_verifications')->insert([
            'user_email' => \auth()->user()->email,
            'phone_number' => $event->phone,
            'otp' => $event->otp,
            'created_at' => Carbon::now(),
        ]);
    }
}
