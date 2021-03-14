<?php

namespace Tests\Unit;

use App\Mail\JustTesting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\TestCase;
use Illuminate\Support\Facades\Mail;

class SendEmailTest extends TestCase
{
    /**@test*/
    public function test_can_send_email()
    {
        Mail::fake();
        Mail::send(new JustTesting());
        Mail::assertSent(JustTesting::class);
        Mail::assertSent(JustTesting::class, function ($mail) {
            $mail->build();
            $this->assertTrue($mail->hasFrom('hello@mailtrap.io'));
            $this->assertTrue($mail->hasCc('hola@mailtrap.io'));
            return true;
        });
    }
}
