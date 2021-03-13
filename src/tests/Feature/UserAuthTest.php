<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserAuthTest extends TestCase
{
    use RefreshDatabase,WithFaker;
    private array $password_confirm=['password_confirmation' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'];
    /**@test*/
    public function test_user_can_register()
    {
        $this->withoutExceptionHandling();
        $user=User::factory()->raw($this->password_confirm);
        $response=$this->json('POST', '/register', $user)->assertStatus(201);
        $this->assertNotEmpty($response['data']['access_token']);
    }
    /**@test*/
    public function test_name_validation_in_registeration()
    {
        $this->withoutExceptionHandling();
        $user=User::factory()->raw(['name'=>'',$this->password_confirm]);
        $response=$this->json('POST', '/register', $user)->assertStatus(401);
        $this->assertEquals("The name field is required.", $response['data']['name'][0]);
    }
    /**@test*/
    public function test_email_required_in_registeration()
    {
        $this->withoutExceptionHandling();
        $user=User::factory()->raw(['email'=>'']);
        $response=$this->json('POST', '/register', $user)->assertStatus(401);
        $this->assertEquals("The email field is required.", $response['data']['email'][0]);
    }
    /**@test*/
    public function test_email_unique_in_registeration()
    {
        $this->withoutExceptionHandling();
        $user=User::factory()->raw($this->password_confirm);
        $this->json('POST', '/register', $user)->assertStatus(201);
        Auth::logout();
        $response=$this->json('POST', '/register', $user)->assertStatus(401);
        $this->assertEquals("The email has already been taken.", $response['data']['email'][0]);
    }
    /**@test*/
    public function test_password_validation()
    {
        $this->withoutExceptionHandling();
        $user=User::factory()->raw(['password'=>'']);
        $response=$this->json('POST', '/register', $user)->assertStatus(401);
        $this->assertEquals("The password field is required.", $response['data']['password'][0]);
    }
    /**@test*/
    public function test_auth_user_cannot_register_again()
    {
        $this->withoutExceptionHandling();
        $this->actingAs(User::factory()->create());
        $user=User::factory()->raw();
        $response=$this->json('POST', '/register', $user);
        $this->assertEquals("already authenticated", $response['message']);
    }
    /**@test*/
    public function test_user_can_login()
    {
        $this->withoutExceptionHandling();
        $user=User::factory()->create();
        $credentials=['email'=>$user->email,'password'=>'password'];
        $response=$this->json('POST', '/login', $credentials)->assertstatus(200)->assertjsonstructure(['message', 'data']);
        $this->assertnotempty($response['data']['access_token']);
    }
    /**@test */
    public function test_auth_user_cannot_login()
    {
        $this->withoutExceptionHandling();
        $user=User::factory()->create();
        $credentials=['email'=>$user->email,'password'=>'password'];
        $response=$this->postJson('login', $credentials)->assertStatus(200);
        $credentials=\array_merge($credentials, ['Authorization'=>'Bearer '.$response['data']['access_token']]);
        $secondResponse=$this->postJson('login', $credentials)->assertStatus(403);
        $this->assertEquals('already authenticated', $secondResponse['message']);
    }
    /**@test*/
    public function test_user_can_logout()
    {
        $this->withoutExceptionHandling();
        $user=User::factory()->create();
        $credentials=['email'=>$user->email,'password'=>'password'];
        $response=$this->json('POST', 'login', $credentials)->assertStatus(200);
        $this->assertNotNull(Auth::guard('api')->user());
        $response = $this->json('POST', 'logout', [], ['Authorization'=>"Bearer ". $response['data']['access_token']])->assertStatus(200);
        $this->assertEquals('success', $response['message']);
        $this->assertNull(Auth::guard('api')->user());
    }
    /**@test*/
    public function test_only_auth_can_logout()
    {
        $this->withoutExceptionHandling();
        $user=User::factory()->create();
        $credentials=['email'=>$user->email,'password'=>'password'];
        $this->postJson('/login', $credentials)->assertStatus(200);
        $this->postJson('/logout')->assertStatus(200);
        $this->expectException('Illuminate\Auth\AuthenticationException');
        $this->postJson('/logout');
    }
    /**@test*/
    public function test_user_can_refresh_its_token()
    {
        $this->withoutExceptionHandling();
        $user=User::factory()->create();
        $credentials=['email'=>$user->email,'password'=>'password'];
        $response=$this->json('POST', 'login', $credentials)->assertStatus(200);
        $newResponse=$this->postJson('/token/refresh', ['Authorization'=>'Bearer '.$response['data']['access_token']])->assertStatus(200);
        $this->assertNotNull($newResponse['data']['access_token']);
    }
    /**@test*/
    public function test_can_return_user_info_with_token()
    {
        $this->withoutExceptionHandling();
        $user=User::factory()->create();
        $credentials=['email'=>$user->email,'password'=>'password'];
        $response=$this->postJson('login', $credentials)->assertStatus(200);
        $newResponse=$this->getJson('/me', ['Authorization'=>'Bearer '.$response['data']['access_token']])->assertStatus(200);
        $this->assertEquals($user->name, $newResponse['data']['name']);
    }
}
