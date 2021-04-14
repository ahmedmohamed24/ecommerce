<?php

namespace Tests;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    private array $password_confirm = ['password_confirmation' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'];

    public function getauthJwtHeader($user = null)
    {
        if (!$user) {
            $user = User::factory()->create();
        }
        $credentials = ['email' => $user->email, 'password' => 'password'];
        $userResponse = $this->postJson('login', $credentials)->assertStatus(200);

        return ['Authorization' => 'Bearer '.$userResponse['data']['access_token']];
    }

    public function createCart()
    {
        $product = Product::factory()->create();

        return $this->postJson('/cart', $product->toArray())->assertSuccessful();
    }

    public function register($user = null)
    {
        if (!$user) {
            $user = User::factory()->raw($this->password_confirm);
        }

        return $this->json('POST', '/register', $user);
    }
}
