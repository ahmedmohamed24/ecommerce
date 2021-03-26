<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CheckoutTest extends TestCase
{
    use WithFaker, RefreshDatabase;
    private array $customerData = ["card_number"=> '4242424242424242',"exp_month"=>'02',"exp_year"=> '24',"cvc"=>'252'];

    public function getauthJwtHeader($user)
    {
        $credentials = ['email' => $user->email, 'password' => 'password'];
        $userResponse = $this->postJson('login', $credentials)->assertStatus(200);
        return ['Authorization' => 'Bearer ' . $userResponse['data']['access_token']];
    }
    /**@test*/
    public function test_can_generate_token()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $authJwt = $this->getauthJwtHeader($user);
        $response = $this->postJson('cart/checkout/token', $this->customerData, $authJwt);
        $response->assertSuccessful();
        $this->assertNotNull($response['data']['id']);
    }
    /**@test*/
    public function test_purchase_the_cart()
    {
        //the whole cycle for buying a product
        $this->withoutExceptionHandling();
        //login
        $user=User::factory()->create();
        $jwtHeader=$this->getauthJwtHeader($user); //authorization with bearer token
        //create category
        $category=Category::factory()->create();
        //attach product to the category
        $product=Product::factory()->raw();
        $product['categories']=[$category->slug];
        $productResponse=$this->postJson('product', $product)->assertSuccessful();
        //add the product to cart
        $this->postJson('/cart', $productResponse['data'])->assertSuccessful();
        //generate payment method (using ajax in frontend) to purchase the product
        $paymentMethodObject=$this->postJson('/cart/checkout/token', $this->customerData)->assertSuccessful();
        $paymentMethodId=$paymentMethodObject['data']['id'];
        //bill using payment method id generated
        $billData = [
            "paymentMethodId" => $paymentMethodId,
            'description' => 'Test cycle',
        ];
        $response = $this->postJson('/cart/checkout', $billData, $jwtHeader);
        $response->assertSuccessful();
        $this->assertEquals('succeeded', $response['data']['status']);
    }
}
