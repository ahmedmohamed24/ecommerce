<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserAttachPhoneEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;
    public string $phone;
    public int $otp;

    /**
     * Create a new event instance.
     */
    public function __construct(string $phone, int $otp)
    {
        $this->phone = $phone;
        $this->otp = $otp;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array|\Illuminate\Broadcasting\Channel
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
