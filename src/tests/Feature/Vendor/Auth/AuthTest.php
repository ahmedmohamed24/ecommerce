<?php

namespace Tests\Feature\Vendor\Auth;

use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class AuthTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;
    public $password = ['password_confirmation' => 'password', 'password' => 'password'];

    // @test
    public function testReturn201StatusWhenRegisterAsVendor()
    {
        $this->withoutExceptionHandling();
        $vendor = Vendor::factory()->raw($this->password);
        $this->postJson('api/' . $this->currentApiVersion . '/vendor/register', $vendor)->assertStatus(201);
    }

    // @test
    public function testVendorDataSavedInDataBaseAfterRegister()
    {
        $this->withoutExceptionHandling();
        $vendor = Vendor::factory()->raw($this->password);
        $this->postJson('api/' . $this->currentApiVersion . '/vendor/register', $vendor);
        $this->assertDatabaseCount('vendors', 1);
        $this->assertDatabaseHas('vendors', ['name' => $vendor['name']]);
    }

    // @test
    public function testSuccesMessageReturnedAfterRegistering()
    {
        $this->withoutExceptionHandling();
        $vendor = Vendor::factory()->raw($this->password);
        $response = $this->postJson('api/' . $this->currentApiVersion . '/vendor/register', $vendor);
        $this->assertEquals('success', $response['message']);
    }

    // @test
    public function testJWTReturnedWhenRegistering()
    {
        $this->withoutExceptionHandling();
        $vendor = Vendor::factory()->raw($this->password);
        $response = $this->postJson('api/' . $this->currentApiVersion . '/vendor/register', $vendor);
        $this->assertNotNull($response['data']['access_token']);
    }

    public function testStatus302ReciviedWhileRegisteringWhileAuthenticated()
    {
        $this->withoutExceptionHandling();
        $vendor = Vendor::factory()->raw($this->password);
        $this->postJson('api/' . $this->currentApiVersion . '/vendor/register', $vendor);
        $this->postJson('api/' . $this->currentApiVersion . '/vendor/register', $vendor)->assertStatus(302);
    }

    // @test
    public function testVendorGet200StatusAfterLogout()
    {
        $this->withoutExceptionHandling();
        $vendor = Vendor::factory()->raw($this->password);
        $this->postJson('api/' . $this->currentApiVersion . '/vendor/register', $vendor);
        $this->postJson('api/' . $this->currentApiVersion . '/vendor/logout')->assertStatus(200);
    }

    // @test
    public function testVendorGet200StatusAfterLogin()
    {
        $this->withoutExceptionHandling();
        Vendor::create($vendor = Vendor::factory()->raw(['password' => \bcrypt('password')]));
        $vendor['password'] = 'password';
        $response = $this->postJson('api/' . $this->currentApiVersion . '/vendor/login', $vendor);
        $response->assertStatus(200);
    }

    // @test
    public function testVendorGet302AfterLoginWhileAuthenticated()
    {
        $this->withoutExceptionHandling();
        Vendor::create($vendor = Vendor::factory()->raw(['password' => \bcrypt('password')]));
        $vendor['password'] = 'password';
        $this->postJson('api/' . $this->currentApiVersion . '/vendor/login', $vendor);
        $response = $this->postJson('api/' . $this->currentApiVersion . '/vendor/login', $vendor);
        $response->assertStatus(302);
    }

    // @test
    public function testOnlyAuthCanLogout()
    {
        $vendor = Vendor::factory()->raw($this->password);
        $this->postJson('api/' . $this->currentApiVersion . '/vendor/logout', $vendor)->assertStatus(403);
    }

    // @test
    public function testCanGetVendorInfo()
    {
        $this->withoutExceptionHandling();
        Vendor::create($vendor = Vendor::factory()->raw(['password' => \bcrypt('password')]));
        $vendor['password'] = 'password';
        $this->postJson('api/' . $this->currentApiVersion . '/vendor/login', $vendor);
        $response = $this->getJson('api/' . $this->currentApiVersion . '/vendor/me', $vendor);
        $this->assertEquals($vendor['name'], $response['data']['name']);
    }

    // @test
    public function testVendorCanRefreshToken()
    {
        $this->withoutExceptionHandling();
        Vendor::create($vendor = Vendor::factory()->raw(['password' => \bcrypt('password'), 'email_verified_at' => Carbon::now()]));
        $vendor['password'] = 'password';
        $this->postJson('api/' . $this->currentApiVersion . '/vendor/login', $vendor);
        $response = $this->getJson('api/' . $this->currentApiVersion . '/vendor/refresh-token', $vendor);
        $this->assertNotNull($response['data']['access_token']);
    }

    public function testVendorCanVerifyEmail()
    {
        $this->withoutExceptionHandling();
        $this->actingAs(Vendor::factory()->create());
        $response = $this->post('api/' . $this->currentApiVersion . '/email/verification-notification')->assertStatus(302);
        self::assertEquals('Verification link sent!', $response['message']);
    }
}
