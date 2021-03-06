<?php

namespace Tests\Feature\User\Auth;

use App\Jobs\SendUserWelcomeEmailJob;
use App\Mail\UserWelcomeEmail;
use App\Models\User;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class UserAuthTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;
    private array $password_confirm = ['password_confirmation' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'];

    // @test
    public function testUserCanRegister()
    {
        $this->withoutExceptionHandling();
        $response = $this->register();
        $response->assertStatus(201);
        $this->assertNotEmpty($response['data']['access_token']);
    }

    // @test
    public function testUserMustVerifyEmail()
    {
        $this->withoutExceptionHandling();
        $this->register();
        $this->get('api/'.$this->currentApiVersion.'/cart')->assertRedirect('api/'.$this->currentApiVersion.'/email/verify')->assertStatus(302);
    }

    // @test
    public function testNameValidationInRegister()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->raw(['name' => '', $this->password_confirm]);
        $response = $this->json('POST', '/api/'.$this->currentApiVersion.'/register', $user)->assertStatus(400);
        $this->assertEquals('The name field is required.', $response['data']['name'][0]);
    }

    // @test
    public function testEmailRequiredInRegister()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->raw(['email' => '']);
        $response = $this->json('POST', '/api/'.$this->currentApiVersion.'/register', $user)->assertStatus(400);
        $this->assertEquals('The email field is required.', $response['data']['email'][0]);
    }

    // @test
    public function testEmailUniqueInRegister()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->raw($this->password_confirm);
        $this->json('POST', '/api/'.$this->currentApiVersion.'/register', $user)->assertStatus(201);
        Auth::logout();
        $response = $this->json('POST', '/api/'.$this->currentApiVersion.'/register', $user)->assertStatus(400);
        $this->assertEquals('The email has already been taken.', $response['data']['email'][0]);
    }

    // @test
    public function testPasswordValidation()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->raw(['password' => '']);
        $response = $this->json('POST', '/api/'.$this->currentApiVersion.'/register', $user)->assertStatus(400);
        $this->assertEquals('The password field is required.', $response['data']['password'][0]);
    }

    // @test
    public function testAuthUserCannotRegisterAgain()
    {
        $this->withoutExceptionHandling();
        $this->actingAs(User::factory()->create());
        $user = User::factory()->raw();
        $response = $this->json('POST', '/api/'.$this->currentApiVersion.'/register', $user);
        $this->assertEquals('already authenticated', $response['message']);
    }

    // @test
    public function testUserCanLogin()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $credentials = ['email' => $user->email, 'password' => 'password'];
        $response = $this->json('POST', '/api/'.$this->currentApiVersion.'/login', $credentials)->assertstatus(200)->assertjsonstructure(['message', 'data']);
        $this->assertnotempty($response['data']['access_token']);
    }

    // @test
    public function testAuthUserCannotLogin()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $credentials = ['email' => $user->email, 'password' => 'password'];
        $response = $this->postJson('api/'.$this->currentApiVersion.'/login', $credentials)->assertStatus(200);
        $credentials = \array_merge($credentials, ['Authorization' => 'Bearer '.$response['data']['access_token']]);
        $secondResponse = $this->postJson('api/'.$this->currentApiVersion.'/login', $credentials)->assertStatus(403);
        $this->assertEquals('already authenticated', $secondResponse['message']);
    }

    // @test
    public function testUserCanLogout()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $credentials = ['email' => $user->email, 'password' => 'password'];
        $response = $this->json('POST', '/api/'.$this->currentApiVersion.'/login', $credentials)->assertStatus(200);
        $this->assertNotNull(Auth::guard('api')->user());
        $response = $this->json('POST', '/api/'.$this->currentApiVersion.'/logout', [], ['Authorization' => 'Bearer '.$response['data']['access_token']])->assertStatus(200);
        $this->assertEquals('success', $response['message']);
        $this->assertNull(Auth::guard('api')->user());
    }

    // @test
    public function testOnlyAuthCanLogout()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $credentials = ['email' => $user->email, 'password' => 'password'];
        $this->postJson('api/'.$this->currentApiVersion.'/login', $credentials)->assertStatus(200);
        $this->postJson('api/'.$this->currentApiVersion.'/logout')->assertStatus(200);
        $this->expectException('Illuminate\Auth\AuthenticationException');
        $this->postJson('api/'.$this->currentApiVersion.'/logout');
    }

    // @test
    public function testUserCanRefreshItsToken()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $credentials = ['email' => $user->email, 'password' => 'password'];
        $response = $this->json('POST', '/api/'.$this->currentApiVersion.'/login', $credentials)->assertStatus(200);
        $newResponse = $this->postJson('api/'.$this->currentApiVersion.'/token/refresh', ['Authorization' => 'Bearer '.$response['data']['access_token']])->assertStatus(200);
        $this->assertNotNull($newResponse['data']['access_token']);
    }

    // @test
    public function testCanReturnUserInfoWithToken()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $credentials = ['email' => $user->email, 'password' => 'password'];
        $response = $this->postJson('api/'.$this->currentApiVersion.'/login', $credentials)->assertStatus(200);
        $newResponse = $this->getJson('api/'.$this->currentApiVersion.'/me', ['Authorization' => 'Bearer '.$response['data']['access_token']])->assertStatus(200);
        $this->assertEquals($user->name, $newResponse['data']['name']);
    }

    public function testUserReceivesWelcomeEmailWhenRegistering()
    {
        $this->withoutExceptionHandling();
        Bus::fake();
        $user = User::factory()->raw(['password_confirmation' => 'password', 'password' => 'password']);
        $response = $this->postJson(route('user.register'), $user);
        $response->assertStatus(201);
        Bus::assertDispatched(SendUserWelcomeEmailJob::class);
    }
}
