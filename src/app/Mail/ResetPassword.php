<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPassword extends Mailable
{
    use Queueable;
    use SerializesModels;
    public string $email;
    public string $token;
    public string $table;

    /**
     * Create a new message instance.
     *
     * @param mixed $token
     * @param mixed $email
     * @param mixed $table
     */
    public function __construct($token, $email, $table)
    {
        $this->email = $email;
        $this->token = $token;
        $this->table = $table;
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
                'url' => route($this->table.'.reset', [$this->email, $this->token]),
                'email' => $this->email,
            ])
        ;
    }
}
