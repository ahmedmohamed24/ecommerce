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
        $this->post('/admin/login', $admin)->assertStatus(200);
    }

    // @test
    public function testEmailIsRequiredToLogin()
    {
        $this->withoutExceptionHandling();
        $admin = Admin::factory()->raw();
        Admin::create($admin);
        $admin['email'] = null;
        $this->post('admin/login', $admin)->assertStatus(400)->assertJson(['message' => 'error', 'data' => ['email' => ['The email field is required.']]]);
    }

    // @test
    public function testPasswordIsRequiredToLogin()
    {
        $this->withoutExceptionHandling();
        $admin = Admin::factory()->raw();
        Admin::create($admin);
        $admin['password'] = null;
        $this->post('admin/login', $admin)->assertStatus(400)->assertJson(['message' => 'error', 'data' => ['password' => ['The password field is required.']]]);
    }

    // @test
    public function testAuthUsersCannotLogin()
    {
        $this->withoutExceptionHandling();
        $admin = Admin::factory()->raw();
        Admin::create($admin);
        $admin['password'] = 'password';
        $this->post('admin/login', $admin)->assertStatus(200);
        $this->assertInstanceOf(Admin::class, Auth::guard('admin')->user());
        $this->post('admin/login', $admin)->assertStatus(302);
    }

    // @test
    public function testCanRegister()
    {
        $this->withoutExceptionHandling();
        $admin = Admin::factory()->raw();
        $admin['password_confirm'] = $admin['password'];
        $this->post('admin/register', $admin)->assertStatus(200)->assertJson(['message' => 'success']);
        $this->assertDatabaseHas('admins', ['name' => $admin['name'], 'email' => $admin['email']]);
    }

    // @test
    public function testRegisterValidation()
    {
        $this->withoutExceptionHandling();
        $admin = Admin::factory()->raw();
        $admin['password_confirm'] = $admin['password'];
        $admin['password'] = '';
        $this->post('admin/register', $admin)->assertStatus(400);
    }

    // @test
    public function testUserRegisterLoginCycle()
    {
        $this->withoutExceptionHandling();
        $admin = Admin::factory()->raw();
        $admin['password_confirm'] = $admin['password'];
        $this->post('admin/register', $admin)->assertStatus(200);
        $this->assertInstanceOf(Admin::class, \auth('admin')->user());
    }

    // @test
    public function testAdminCanLogout()
    {
        $this->withoutExceptionHandling();
        $admin = Admin::factory()->raw();
        Admin::create($admin);
        $admin['password'] = 'password';
        $this->post('/admin/login', $admin)->assertStatus(200);
        $this->post('/admin/logout', ['id' => \auth('admin')->id()])->assertStatus(200);
        $this->assertNull(\auth('admin')->user());
    }

    // @test
    public function testOnlyAuthCanLogout()
    {
        $admin = Admin::factory()->raw();
        Admin::create($admin);
        $admin['password'] = 'password';
        $this->post('/admin/logout', [])->assertStatus(403);
        $this->assertNull(\auth('admin')->user());
    }

    // @test
    public function testAdminRecievesEmailWhenAskToResetPassword()
    {
        $this->withoutExceptionHandling();
        $admin = Admin::factory()->create();
        $this->json('POST', '/admin/password/email', ['email' => $admin->email])->assertStatus(200)->assertJson(['message' => 'We have emailed your password reset link!']);
    }
}
