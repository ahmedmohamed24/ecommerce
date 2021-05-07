<?php

namespace Tests;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    private array $password_confirm = ['password_confirmation' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'];

    public function getAuthJwtHeader($user = null)
    {
        if (!$user) {
            $user = User::factory()->create(['email_verified_at' => Carbon::now()]);
        }
        $credentials = ['email' => $user->email, 'password' => 'password'];
        $userResponse = $this->postJson('login', $credentials)->assertStatus(200);

        return ['Authorization' => 'Bearer '.$userResponse['data']['access_token']];
    }

    public function createCart()
    {
        $product = Product::factory()->create(['owner' => auth('vendor')->check() ? \auth()->id() : (Vendor::factory()->create())->id]);

        return $this->postJson('/cart', $product->toArray())->assertSuccessful();
    }

    public function register($user = null)
    {
        if (!$user) {
            $user = User::factory()->raw(\array_merge($this->password_confirm, ['email_verified_at' => Carbon::now()]));
        }

        return $this->json('POST', '/register', $user);
    }

    public function attachCategories($product)
    {
        if (!\array_key_exists('categories', $product)) {
            $product['categories'] = [(Category::factory()->create())->slug];
        } else {
            \array_push($product['categories'], (Category::factory()->create())->slug);
        }

        return $product;
    }
}
