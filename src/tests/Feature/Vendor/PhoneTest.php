<?php

namespace Tests\Feature\Vendor;

use App\Models\Vendor;
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
        $this->withoutExceptionHandling();
        $vendor = Vendor::factory()->raw(['password_confirmation' => 'password']);
        $this->postJson('/vendor/register', $vendor)->assertSuccessful();
        $response = $this->postJson('/vendor/attach-phone', ['phone' => '+201212924690']);
        $response->assertSuccessful();
    }

    public function testVendorPhoneIsSavedInDB()
    {
        $this->withoutExceptionHandling();
        $vendor = Vendor::factory()->raw(['password_confirmation' => 'password']);
        $this->postJson('/vendor/register', $vendor)->assertSuccessful();
        $phone = '+201212924690';
        $this->postJson('/vendor/attach-phone', ['phone' => $phone]);
        $this->assertDatabaseHas('vendors', ['phone' => $phone]);
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
}
