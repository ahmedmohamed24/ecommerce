<?php

namespace Tests\Feature\Vendor;

use App\Events\VendorAddedPhoneEvent;
use App\Models\Vendor;
use Carbon\Carbon;
use DB;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class PhoneTest extends TestCase
{
    use RefreshDatabase;

    // @test
    public function testVendorRecieves200StatusWhenAttachPhone()
    {
        Event::fake();
        $this->withoutExceptionHandling();
        $vendor = Vendor::factory()->raw(['password_confirmation' => 'password']);
        $this->postJson('/vendor/register', $vendor)->assertSuccessful();
        $response = $this->postJson('/vendor/attach-phone', ['phone' => '+201212924690']);
        $response->assertSuccessful();
    }

    // @test
    public function testVendorCanOnlyAddaValidPhoneWithLength13()
    {
        $this->withoutExceptionHandling();
        $vendor = Vendor::factory()->raw(['password_confirmation' => 'password']);
        $this->postJson('/vendor/register', $vendor)->assertSuccessful();
        $phone = '+20112924690';
        $response = $this->postJson('/vendor/attach-phone', ['phone' => $phone]);
        $response->assertStatus(406);
        $response->assertJsonValidationErrors('phone');
    }

    // @test
    public function testOnlyAuthVendorCanAddPhone()
    {
        $phone = '+20112924690';
        $response = $this->postJson('/vendor/attach-phone', ['phone' => $phone]);
        $response->assertStatus(403);
    }

    // @test
    public function testVendorAddedPhoneEventIsDispatchedWhenVendorAddPhone()
    {
        Event::fake();
        $this->withoutExceptionHandling();
        $vendor = Vendor::factory()->raw(['password_confirmation' => 'password']);
        $this->postJson('/vendor/register', $vendor)->assertSuccessful();
        $phone = '+201212924690';
        $this->postJson('/vendor/attach-phone', ['phone' => $phone]);
        Event::assertDispatched(VendorAddedPhoneEvent::class);
    }

    // @test
    public function testVendorPhoneIsSavedToDB()
    {
        $this->withoutExceptionHandling();
        $vendor = Vendor::factory()->raw(['password_confirmation' => 'password']);
        $this->postJson('/vendor/register', $vendor)->assertSuccessful();
        $phone = '+201212924690';
        $this->postJson('/vendor/attach-phone', ['phone' => $phone]);
        $this->assertDatabaseCount('sms_verifications', 1);
    }

    // @test
    public function testVendorCannotAddPhoneIfHisPhoneIsVerified()
    {
        Event::fake();
        $this->withoutExceptionHandling();
        $vendor = Vendor::factory()->raw(['password_confirmation' => 'password']);
        $this->postJson('/vendor/register', $vendor)->assertSuccessful();
        Vendor::first()->update(['phone_verified_at' => Carbon::now()]);
        $phone = '+201212924690';
        $response = $this->postJson('/vendor/attach-phone', ['phone' => $phone]);
        $response->assertStatus(406);
        $this->assertEquals('Your phone is already verified.', $response['message']);
    }

    // @test
    public function testVendorCanUseTheOTPToVerifyPhone()
    {
        Event::fake();
        $this->withoutExceptionHandling();
        $vendor = Vendor::factory()->raw(['password_confirmation' => 'password']);
        $this->postJson('/vendor/register', $vendor)->assertSuccessful();
        $phone = '+201212924690';
        $otp = rand(100000, 999999);
        $this->postJson('/vendor/attach-phone', ['phone' => $phone]);
        DB::table('sms_verifications')->insert([
            'user_email' => \auth('vendor')->user()->email,
            'phone_number' => $phone,
            'otp' => $otp,
            'created_at' => Carbon::now(),
        ]);
        $response = $this->postJson('vendor/verify-phone', ['phone' => $phone, 'otp' => $otp]);
        $response->assertStatus(200);
        $this->assertNotNull(Vendor::first()->phone_verified_at);
    }

    public function testVendorOTPAndPhoneMustBeValid()
    {
        Event::fake();
        $this->withoutExceptionHandling();
        $vendor = Vendor::factory()->raw(['password_confirmation' => 'password']);
        $this->postJson('/vendor/register', $vendor)->assertSuccessful();
        $phone = '+201212924690';
        $otp = rand(100000, 999999);
        $this->postJson('/vendor/attach-phone', ['phone' => $phone]);
        DB::table('sms_verifications')->insert([
            'user_email' => \auth('vendor')->user()->email,
            'phone_number' => $phone,
            'otp' => 5413125, //error
            'created_at' => Carbon::now(),
        ]);
        $response = $this->postJson('vendor/verify-phone', ['phone' => $phone, 'otp' => $otp]);
        $response->assertStatus(406);
    }

    public function testOtpIsDeletedAfterVerification()
    {
        Event::fake();
        $this->withoutExceptionHandling();
        $vendor = Vendor::factory()->raw(['password_confirmation' => 'password']);
        $this->postJson('/vendor/register', $vendor)->assertSuccessful();
        $phone = '+201212924690';
        $otp = rand(100000, 999999);
        $this->postJson('/vendor/attach-phone', ['phone' => $phone]);
        DB::table('sms_verifications')->insert([
            'user_email' => \auth('vendor')->user()->email,
            'phone_number' => $phone,
            'otp' => $otp, //error
            'created_at' => Carbon::now(),
        ]);
        $this->postJson('vendor/verify-phone', ['phone' => $phone, 'otp' => $otp]);
        $this->assertDatabaseCount('sms_verifications', 0);
    }

    // @test
    public function testSuccessMsgIsRecievedAfterPhoneVerification()
    {
        Event::fake();
        $this->withoutExceptionHandling();
        $vendor = Vendor::factory()->raw(['password_confirmation' => 'password']);
        $this->postJson('/vendor/register', $vendor)->assertSuccessful();
        $phone = '+201212924690';
        $otp = rand(100000, 999999);
        $this->postJson('/vendor/attach-phone', ['phone' => $phone]);
        DB::table('sms_verifications')->insert([
            'user_email' => \auth('vendor')->user()->email,
            'phone_number' => $phone,
            'otp' => $otp, //error
            'created_at' => Carbon::now(),
        ]);
        $response = $this->postJson('vendor/verify-phone', ['phone' => $phone, 'otp' => $otp]);
        $this->assertEquals('phone successfully verified.', $response['message']);
    }
}
