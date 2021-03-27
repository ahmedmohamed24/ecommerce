<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    public function getauthJwtHeader($user = null)
    {
        if (!$user) {
            $user = User::factory()->create();
        }
        $credentials = ['email' => $user->email, 'password' => 'password'];
        $userResponse = $this->postJson('login', $credentials)->assertStatus(200);
        return ['Authorization' => 'Bearer ' . $userResponse['data']['access_token']];
    }
}
