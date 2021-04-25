<?php

namespace App\Providers;

use App\Events\VendorAddedPhoneEvent;
use App\Listeners\VendorSaveOTPListener;
use App\Listeners\VendorSendSMSListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        VendorAddedPhoneEvent::class => [
            VendorSendSMSListener::class,
            VendorSaveOTPListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot()
    {
    }
}
