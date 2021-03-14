<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPassword extends Mailable
{
    use Queueable, SerializesModels;
    public string $email;
    public string $token;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($token, $email)
    {
        $this->email=$email;
        $this->token=$token;
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
                    'url' => route('reset', [$this->email,$this->token]),
                    'email' =>$this->email,
                ]);
    }
}
