<?php

namespace App\Jobs;

use App\Mail\ResetPasswordMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ResetPasswordJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $token;

    protected $email;

    protected $guard;

    /**
     * Create a new job instance.
     *
     * @param mixed $token
     * @param mixed $email
     * @param mixed $guard
     */
    public function __construct($token, $email, $guard)
    {
        $this->token = $token;
        $this->email = $email;
        $this->guard = $guard;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Mail::to($this->email)->send(new ResetPasswordMail($this->token, $this->email, $this->guard));
    }
}
