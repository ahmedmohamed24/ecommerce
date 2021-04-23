<?php

namespace Tests\Feature\Vendor;

use App\Models\Vendor;
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

    // @test
    public function testReturn201StatusWhenRegisterAsVendor()
    {
        $this->withoutExceptionHandling();
        $vendor = Vendor::factory()->raw(['password_confirmation' => 'password']);
        $this->postJson('vendor/register', $vendor)->assertStatus(201);
    }

    // @test
    public function testVendorDataSavedInDataBaseAfterRegister()
    {
        $this->withoutExceptionHandling();
        $vendor = Vendor::factory()->raw(['password_confirmation' => 'password']);
        $this->postJson('vendor/register', $vendor);
        $this->assertDatabaseCount('vendors', 1);
        $this->assertDatabaseHas('vendors', ['name' => $vendor['name']]);
    }

    // @test
    public function testSuccesMessageReturnedAfterRegistering()
    {
        $this->withoutExceptionHandling();
        $vendor = Vendor::factory()->raw(['password_confirmation' => 'password']);
        $response = $this->postJson('vendor/register', $vendor);
        $this->assertEquals('success', $response['message']);
    }

    // @test
    public function testJWTReturnedWhenRegistering()
    {
        $this->withoutExceptionHandling();
        $vendor = Vendor::factory()->raw(['password_confirmation' => 'password']);
        $response = $this->postJson('vendor/register', $vendor);
        $this->assertNotNull($response['data']['access_token']);
    }

    public function testStatus302ReciviedWhileRegisteringWhileAuthenticated()
    {
        $this->withoutExceptionHandling();
        $vendor = Vendor::factory()->raw(['password_confirmation' => 'password']);
        $this->postJson('vendor/register', $vendor);
        $this->postJson('vendor/register', $vendor)->assertStatus(302);
    }

    // @test
    public function testVendorGet200StatusAfterLogout()
    {
        $this->withoutExceptionHandling();
        $vendor = Vendor::factory()->raw(['password_confirmation' => 'password']);
        $this->postJson('vendor/register', $vendor);
        $this->postJson('vendor/logout')->assertStatus(200);
    }

    // @test
    public function testVendorGet200StatusAfterLogin()
    {
        $this->withoutExceptionHandling();
        Vendor::create($vendor = Vendor::factory()->raw(['password' => \bcrypt('password')]));
        $vendor['password'] = 'password';
        $response = $this->postJson('vendor/login', $vendor);
        $response->assertStatus(200);
    }

    // @test
    public function testVendorGet302AfterLoginWhileAuthenticated()
    {
        $this->withoutExceptionHandling();
        Vendor::create($vendor = Vendor::factory()->raw(['password' => \bcrypt('password')]));
        $vendor['password'] = 'password';
        $this->postJson('vendor/login', $vendor);
        $response = $this->postJson('vendor/login', $vendor);
        $response->assertStatus(302);
    }

    // @test
    public function testOnlyAuthCanLogout()
    {
        $vendor = Vendor::factory()->raw(['password_confirmation' => 'password']);
        $this->postJson('vendor/logout', $vendor)->assertStatus(403);
    }

    // @test
    public function testCanGetVendorInfo()
    {
        $this->withoutExceptionHandling();
        Vendor::create($vendor = Vendor::factory()->raw(['password' => \bcrypt('password')]));
        $vendor['password'] = 'password';
        $this->postJson('vendor/login', $vendor);
        $response = $this->getJson('vendor/me', $vendor);
        $this->assertEquals($vendor['name'], $response['data']['name']);
    }

    // @test
    public function testVendorCanRefreshToken()
    {
        $this->withoutExceptionHandling();
        Vendor::create($vendor = Vendor::factory()->raw(['password' => \bcrypt('password')]));
        $vendor['password'] = 'password';
        $this->postJson('vendor/login', $vendor);
        $response = $this->getJson('vendor/refresh-token', $vendor);
        $this->assertNotNull($response['data']['access_token']);
    }
}
