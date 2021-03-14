<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserResetPasswordTest extends TestCase
{
    use RefreshDatabase,WithFaker;
    const  RESET_REQUEST_URL="/password/request/reset";
    const  RESET_URL='/password/reset';

    /**@test*/
    public function test_user_can_send_request_to_reset_password()
    {
        $this->withoutExceptionHandling();
        $user=User::factory()->create();
        $response=$this->postJson(self::RESET_REQUEST_URL, ['email'=>$user->email])->assertStatus(200);
        $this->assertEquals($response['message'], 'success');
    }
    /**@test*/
    public function test_user_should_wait_before_new_request()
    {
        $this->withoutExceptionHandling();
        $user=User::factory()->create();
        $this->postJson(self::RESET_REQUEST_URL, ['email'=>$user->email])->assertStatus(200);
        $this->postJson(self::RESET_REQUEST_URL, ['email'=>$user->email])->assertStatus(Response::HTTP_FORBIDDEN);
    }
    /**@test*/
    public function test_password_validation_in_resetting_password()
    {
        $this->withoutExceptionHandling();
        $user=User::factory()->create();
        $this->postJson(self::RESET_REQUEST_URL, ['email'=>$user->email])->assertStatus(Response::HTTP_OK);
        $dbResponse=DB::table('password_resets')->latest()->first();
        $urlResponse=$this->json('POST', self::RESET_URL, ['email'=>$dbResponse->email,'token'=>$dbResponse->token,'password'=>'ahmed12345','password_confirmation'=>'hafdkkajl'])->assertStatus(Response::HTTP_FORBIDDEN);
        $this->assertNotNull($urlResponse['data']['password']);
    }
    /**@test*/
    public function test_email_and_token_must_exist_in_reset_password_table()
    {
        $this->withoutExceptionHandling();
        $user=User::factory()->create();
        $this->postJson(self::RESET_REQUEST_URL, ['email'=>$user->email])->assertStatus(Response::HTTP_OK);
        $dbResponse=DB::table('password_resets')->latest()->first();
        $urlResponse=$this->json('POST', self::RESET_URL, ['email'=>'dummy_email','token'=>$dbResponse->token,'password'=>'ahmed12345','password_confirmation'=>'ahmed12345'])->assertStatus(Response::HTTP_FORBIDDEN);
        $this->assertNotNull($urlResponse['data']['email']);
    }
    /**@test*/
    public function test_user_can_reset_password_using_email_and_token()
    {
        $this->withoutExceptionHandling();
        $user=User::factory()->create();
        $this->postJson(self::RESET_REQUEST_URL, ['email'=>$user->email])->assertStatus(200);
        $dbResponse=DB::table('password_resets')->latest()->first();
        $this->json('POST', self::RESET_URL, ['email'=>$user->email,'token'=>$dbResponse->token,'password'=>'ahmed12345','password_confirmation'=>'ahmed12345'])->assertStatus(Response::HTTP_ACCEPTED);
        $urlResponse=$this->postJson('login', ['email'=>$user->email,'password'=>'ahmed12345']);
        $this->assertNotEmpty($urlResponse['data']['access_token']);
    }
}
