<?php

namespace Tests\Feature\User;

use App\Events\UserAttachPhoneEvent;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class TwilioTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    // @test
    public function testUserCanAddPhoneNumber()
    {
        Event::fake();
        $this->withoutExceptionHandling();
        $authHeader = $this->getAuthJwtHeader();
        $phone = ['phone' => '+201212924690'];
        $response = $this->postJson('/phone-add', $phone, $authHeader);
        $response->assertRedirect();
    }

    public function testUserAttachEventPhoneIsFired()
    {
        $this->withoutExceptionHandling();
        Event::fake();
        $authHeader = $this->getAuthJwtHeader();
        $phone = ['phone' => '+201212924690'];
        $this->postJson('/phone-add', $phone, $authHeader);
        Event::assertDispatched(UserAttachPhoneEvent::class);
    }

    // @test
    public function testCannotRequestOTPBefore1Minute()
    {
        Event::fake();
        $this->withoutExceptionHandling();
        $authHeader = $this->getAuthJwtHeader();
        $phone = ['phone' => '+201212924690'];
        DB::table('sms_verifications')->insert([
            'user_email' => 'a@gmail.com',
            'phone_number' => '+201212924690',
            'otp' => 2345,
            'created_at' => Carbon::now(),
        ]);
        $response = $this->postJson('/phone-add', $phone, $authHeader)->assertStatus(400);
        $this->assertEquals('please wait one minute before requesting another OTP.', $response['message']);
    }

    public function testVerifiedNumberCannotVerifiedAgain()
    {
        Event::fake();
        $this->withoutExceptionHandling();
        $authHeader = $this->getAuthJwtHeader();
        $user = User::findOrFail(Auth::id());
        $user->update([
            'phone' => '+201212924690',
            'phone_verified_at' => Carbon::now(),
        ]);
        $phone = ['phone' => '+201212924690'];
        $response = $this->postJson('/phone-add', $phone, $authHeader);
        $response->assertStatus(400);
        $this->assertEquals('Email is already verified.', $response['message']);
    }

    // @test
    public function testUserRecieve200WhenVerifyingPhoneWithTheRecievedOTP()
    {
        Event::fake();
        $this->withoutExceptionHandling();
        $authHeader = $this->getAuthJwtHeader();
        $user = User::findOrFail(Auth::id());
        $phone = '+201212924690';
        $otp = rand(100000, 999999);
        //simulate event behavior
        $user->update(['phone' => $phone]);
        DB::table('sms_verifications')->insert([
            'user_email' => \auth()->user()->email,
            'phone_number' => $phone,
            'otp' => $otp,
            'created_at' => Carbon::now(),
        ]);
        $this->postJson('/phone-verify', ['otp' => $otp, 'phone' => $phone], $authHeader)->assertSuccessful();
    }

    // @test
    public function testUserRecieveSuccessMessageWhenVerifyingPhoneWithTheRecievedOTP()
    {
        Event::fake();
        $this->withoutExceptionHandling();
        $authHeader = $this->getAuthJwtHeader();
        $user = User::findOrFail(Auth::id());
        $phone = '+201212924690';
        $otp = rand(100000, 999999);
        //simulate event behavior
        $user->update(['phone' => $phone]);
        DB::table('sms_verifications')->insert([
            'user_email' => \auth()->user()->email,
            'phone_number' => $phone,
            'otp' => $otp,
            'created_at' => Carbon::now(),
        ]);
        $response = $this->postJson('/phone-verify', ['otp' => $otp, 'phone' => $phone], $authHeader);
        $this->assertEquals('successfully verified', $response['message']);
    }

    public function testUserUserCanVerifyPhoneWithOnlyValidOTP()
    {
        Event::fake();
        $this->withoutExceptionHandling();
        $authHeader = $this->getAuthJwtHeader();
        $user = User::findOrFail(Auth::id());
        $phone = '+201212924690';
        $otp = rand(100000, 999999);
        //simulate event behavior
        $user->update(['phone' => $phone]);
        DB::table('sms_verifications')->insert([
            'user_email' => \auth()->user()->email,
            'phone_number' => $phone,
            'otp' => 425263,
            'created_at' => Carbon::now(),
        ]);
        $response = $this->postJson('/phone-verify', ['otp' => $otp, 'phone' => $phone], $authHeader);
        $response->assertStatus(406);
        $this->assertEquals('These credentials does not match our records', $response['message']);
    }
}
