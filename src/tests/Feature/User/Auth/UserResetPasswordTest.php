<?php

namespace Tests\Feature\User\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class UserResetPasswordTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;
    const RESET_REQUEST_URL = 'api/v1/password/request/reset';
    const RESET_URL = 'api/v1/password/reset';

    // @test
    public function testUserCanSendRequestToResetPassword()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $response = $this->postJson(self::RESET_REQUEST_URL, ['email' => $user->email])->assertStatus(200);
        $this->assertEquals($response['message'], 'success');
    }

    // @test
    public function testUserShouldWaitBeforeNewRequest()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $this->postJson(self::RESET_REQUEST_URL, ['email' => $user->email])->assertStatus(200);
        $this->postJson(self::RESET_REQUEST_URL, ['email' => $user->email])->assertStatus(Response::HTTP_FORBIDDEN);
    }

    // @test
    public function testPasswordValidationInResettingPassword()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $this->postJson(self::RESET_REQUEST_URL, ['email' => $user->email])->assertStatus(Response::HTTP_OK);
        $dbResponse = DB::table('password_resets')->latest()->first();
        $urlResponse = $this->json('POST', self::RESET_URL, ['email' => $dbResponse->email, 'token' => $dbResponse->token, 'password' => 'ahmed12345', 'password_confirmation' => 'hafdkkajl'])->assertStatus(Response::HTTP_FORBIDDEN);
        $this->assertNotNull($urlResponse['data']['password']);
    }

    // @test
    public function testEmailAndTokenMustExistInResetPasswordTable()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $this->postJson(self::RESET_REQUEST_URL, ['email' => $user->email])->assertStatus(Response::HTTP_OK);
        $dbResponse = DB::table('password_resets')->latest()->first();
        $urlResponse = $this->json('POST', self::RESET_URL, ['email' => 'dummy_email', 'token' => $dbResponse->token, 'password' => 'ahmed12345', 'password_confirmation' => 'ahmed12345'])->assertStatus(Response::HTTP_FORBIDDEN);
        $this->assertNotNull($urlResponse['data']['email']);
    }

    // @test
    public function testUserCanResetPasswordUsingEmailAndToken()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $this->postJson(self::RESET_REQUEST_URL, ['email' => $user->email])->assertStatus(200);
        $dbResponse = DB::table('password_resets')->latest()->first();
        $this->json('POST', self::RESET_URL, ['email' => $user->email, 'token' => $dbResponse->token, 'password' => 'ahmed12345', 'password_confirmation' => 'ahmed12345'])->assertStatus(Response::HTTP_ACCEPTED);
        $urlResponse = $this->postJson('api/' . $this->currentApiVersion . '/login', ['email' => $user->email, 'password' => 'ahmed12345']);
        $this->assertNotEmpty($urlResponse['data']['access_token']);
    }
}
