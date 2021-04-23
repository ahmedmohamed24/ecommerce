<?php

namespace Tests\Feature\Vendor;

use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class PasswordResetTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    // @test
    public function testVendorGetStatus200WhenRequestEmailToResetPassword()
    {
        $this->withoutExceptionHandling();
        $vendor = Vendor::factory()->create();
        $response = $this->postJson('/vendor/reset-password/request', ['email' => $vendor->email]);
        $response->assertStatus(200);
    }

    // @test
    public function testVendorCanRestPasswordUsingTokenSentInEmail()
    {
        $this->withoutExceptionHandling();
        $vendor = Vendor::factory()->create();
        $this->postJson('/vendor/reset-password/request', ['email' => $vendor->email]);
        $token = DB::table('password_resets')->first();
        $data = [
            'email' => $vendor->email,
            'password' => 'password1',
            'password_confirmation' => 'password1',
            'token' => $token->token,
        ];
        $this->postJson('/vendor/reset-password', $data)->assertStatus(202);
        $this->postJson('/vendor/login', ['email' => $vendor->email, 'password' => 'password1'])->assertStatus(200);
    }
}
