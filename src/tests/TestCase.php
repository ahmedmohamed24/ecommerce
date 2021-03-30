<?php

namespace Tests;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    const SHOPPING_INFO = [
        'stripeToken' => 'tok_visa',
        'address' => 'address line 1',
        'postal_zip' => 13123,
    ];

    public function purchaseProduct()
    {
        $jwtHeader = $this->getauthJwtHeader(); //authorization with bearer token
        //create category
        $category = Category::factory()->create();
        //attach product to the category
        $product = Product::factory()->raw();
        $product['categories'] = [$category->slug];
        $productResponse = $this->postJson('product', $product)->assertSuccessful();
        //add the product to cart
        $this->postJson('/cart', $productResponse['data'])->assertSuccessful();
        //bill using payment
        return $this->postJson('/cart/checkout', self::SHOPPING_INFO, $jwtHeader);
    }

    public function getauthJwtHeader($user = null)
    {
        if (!$user) {
            $user = User::factory()->create();
        }
        $credentials = ['email' => $user->email, 'password' => 'password'];
        $userResponse = $this->postJson('login', $credentials)->assertStatus(200);

        return ['Authorization' => 'Bearer '.$userResponse['data']['access_token']];
    }
}
