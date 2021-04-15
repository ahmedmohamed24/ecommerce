<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class TwillioTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    // @test
    public function testUserCanAddPhoneNumber()
    {
        $this->withoutExceptionHandling();
        $authHeader = $this->getauthJwtHeader();
        $phone = ['phone' => '+201212924690'];
        $response = $this->postJson('/phone/add', $phone, $authHeader);
        $response->assertRedirect();
        $this->assertDatabaseCount('sms_verifications', 1);
    }

    // @test
    public function testCannotRequestOTPBefore1Minute()
    {
        $this->withoutExceptionHandling();
        $authHeader = $this->getauthJwtHeader();
        $phone = ['phone' => '+201212924690'];
        DB::table('sms_verifications')->insert([
            'user_email' => 'a@gmail.com',
            'phone_number' => '+201212924690',
            'otp' => 2345,
            'created_at' => Carbon::now(),
        ]);
        $response = $this->postJson('/phone/add', $phone, $authHeader)->assertStatus(400);
        $this->assertEquals('please wait one minute berfore requesting another OTP.', $response['message']);
    }

    public function testVerifiedNumberCannotVerifiedAgain()
    {
        $this->withoutExceptionHandling();
        $authHeader = $this->getauthJwtHeader();
        $user = User::findOrFail(Auth::id());
        $user->update([
            'phone' => '1231312412',
            'phone_verified_at' => Carbon::now(),
        ]);
        $phone = ['phone' => '1231312412'];
        $response = $this->postJson('/phone/add', $phone, $authHeader)->assertStatus(400);
        $this->assertEquals('Email is already verified.', $response['message']);
    }
}
