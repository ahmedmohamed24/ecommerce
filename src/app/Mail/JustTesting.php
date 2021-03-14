<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class JustTesting extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function build()
    {
        return $this->from('hello@mailtrap.io')
                ->to('bonjour@mailtrap.io')
                ->cc('hola@mailtrap.io')
                   ->subject('Auf Wiedersehen')
                   ->markdown('emails.test')
                   ->with([
                     'name' => 'New Mailtrap User',
                     'link' => 'https://mailtrap.io/inboxes'
                   ]);
    }
}
