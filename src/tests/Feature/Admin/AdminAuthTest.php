<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class AdminAuthTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    // @test
    public function testAdminCanLogin()
    {
        $this->withoutExceptionHandling();
        $admin = Admin::factory()->raw();
        Admin::create($admin);
        $admin['password'] = 'password';
        $this->post('api/' . $this->currentApiVersion . '/admin/login', $admin)->assertStatus(200);
    }

    // @test
    public function testEmailIsRequiredToLogin()
    {
        $this->withoutExceptionHandling();
        $admin = Admin::factory()->raw();
        Admin::create($admin);
        $admin['email'] = null;
        $this->post('api/' . $this->currentApiVersion . '/admin/login', $admin)->assertStatus(400)->assertJson(['message' => 'error', 'data' => ['email' => ['The email field is required.']]]);
    }

    // @test
    public function testPasswordIsRequiredToLogin()
    {
        $this->withoutExceptionHandling();
        $admin = Admin::factory()->raw();
        Admin::create($admin);
        $admin['password'] = null;
        $this->post('api/' . $this->currentApiVersion . '/admin/login', $admin)->assertStatus(400)->assertJson(['message' => 'error', 'data' => ['password' => ['The password field is required.']]]);
    }

    // @test
    public function testAuthUsersCannotLogin()
    {
        $this->withoutExceptionHandling();
        $admin = Admin::factory()->raw();
        Admin::create($admin);
        $admin['password'] = 'password';
        $this->post('api/' . $this->currentApiVersion . '/admin/login', $admin)->assertStatus(200);
        $this->assertInstanceOf(Admin::class, Auth::guard('admin')->user());
        $this->post('api/' . $this->currentApiVersion . '/admin/login', $admin)->assertStatus(302);
    }

    // @test
    public function testAdminCanLogout()
    {
        $this->withoutExceptionHandling();
        $admin = Admin::factory()->raw();
        Admin::create($admin);
        $admin['password'] = 'password';
        $this->post('api/' . $this->currentApiVersion . '/admin/login', $admin)->assertStatus(200);
        $this->post('api/' . $this->currentApiVersion . '/admin/logout', ['id' => \auth('admin')->id()])->assertStatus(200);
        $this->assertNull(\auth('admin')->user());
    }

    // @test
    public function testOnlyAuthCanLogout()
    {
        $admin = Admin::factory()->raw();
        Admin::create($admin);
        $admin['password'] = 'password';
        $this->post('api/' . $this->currentApiVersion . '/admin/logout', [])->assertStatus(403);
        $this->assertNull(\auth('admin')->user());
    }
}
