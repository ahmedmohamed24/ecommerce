<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;
    const RESET_REQUEST_URL = '/api/v1/admin/reset-password/request';
    const RESET_URL = '/api/v1/admin/reset-password/create-password';

    // @test
    public function testAdminCanSendRequestToResetPassword()
    {
        $this->withoutExceptionHandling();
        Event::fake();
        $admin = Admin::factory()->create();
        $response = $this->postJson(self::RESET_REQUEST_URL, ['email' => $admin->email]);
        $response->assertStatus(200);
        $this->assertEquals($response['message'], 'success');
    }

    // @test
    public function testAdminShouldWaitBeforeNewRequest()
    {
        $this->withoutExceptionHandling();
        Event::fake();
        $admin = Admin::factory()->create();
        $this->postJson(self::RESET_REQUEST_URL, ['email' => $admin->email])->assertStatus(200);
        $this->postJson(self::RESET_REQUEST_URL, ['email' => $admin->email])->assertStatus(Response::HTTP_FORBIDDEN);
    }

    // @test
    public function testPasswordValidationInResettingPassword()
    {
        $this->withoutExceptionHandling();
        Event::fake();
        $admin = Admin::factory()->create();
        $this->postJson(self::RESET_REQUEST_URL, ['email' => $admin->email])->assertStatus(Response::HTTP_OK);
        $dbResponse = DB::table('password_resets')->latest()->first();
        $urlResponse = $this->json('POST', self::RESET_URL, ['email' => $dbResponse->email, 'token' => $dbResponse->token, 'password' => 'ahmed12345', 'password_confirmation' => 'hafdkkajl'])->assertStatus(Response::HTTP_FORBIDDEN);
        $this->assertNotNull($urlResponse['data']['password']);
    }

    // @test
    public function testEmailAndTokenMustExistInResetPasswordTable()
    {
        $this->withoutExceptionHandling();
        Event::fake();
        $admin = Admin::factory()->create();
        $this->postJson(self::RESET_REQUEST_URL, ['email' => $admin->email])->assertStatus(Response::HTTP_OK);
        $dbResponse = DB::table('password_resets')->latest()->first();
        $urlResponse = $this->json('POST', self::RESET_URL, ['email' => 'dummy_email', 'token' => $dbResponse->token, 'password' => 'ahmed12345', 'password_confirmation' => 'ahmed12345'])->assertStatus(Response::HTTP_FORBIDDEN);
        $this->assertNotNull($urlResponse['data']['email']);
    }

    // @test
    public function testAdminCanResetPasswordUsingEmailAndToken()
    {
        $this->withoutExceptionHandling();
        Event::fake();
        $admin = Admin::factory()->create();
        $this->postJson(self::RESET_REQUEST_URL, ['email' => $admin->email])->assertStatus(200);
        $dbResponse = DB::table('password_resets')->latest()->first();
        $this->json('POST', self::RESET_URL, ['email' => $admin->email, 'token' => $dbResponse->token, 'password' => 'ahmed12345', 'password_confirmation' => 'ahmed12345'])->assertStatus(Response::HTTP_ACCEPTED);
        $urlResponse = $this->postJson('api/' . $this->currentApiVersion . '/admin/login', ['email' => $admin->email, 'password' => 'ahmed12345']);
        $this->assertNotEmpty($urlResponse['data']['access_token']);
    }
}
