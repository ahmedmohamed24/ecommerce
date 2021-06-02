<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
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
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('egy@store.com')
            ->markdown('emails.reset', [
                'url' => route($this->guard.'.reset', [$this->email, $this->token]),
                'email' => $this->email,
            ])
        ;
    }
}
