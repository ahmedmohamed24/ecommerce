<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Charge;
use Hamcrest\Type\IsNumeric;
use Illuminate\Support\Facades\DB;

use Illuminate\Foundation\Testing\WithFaker;
use function PHPUnit\Framework\assertIsNumeric;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CheckoutTest extends TestCase
{
    use WithFaker, RefreshDatabase;
    const SHOPPING_INFO = [
        'stripeToken' => 'tok_visa',
        'address' => 'address line 1',
        'postal_zip' => 13123
    ];
    /**@test*/
    public function test_can_get_account_balance()
    {
        $this->withoutExceptionHandling();
        $response = $this->getJson('/stripe/balance')->assertSuccessful();
        assertIsNumeric($response['data']['available'][0]['amount']);
    }
    /**@test*/
    public function test_can_get_balance_transactions()
    {
        $this->withoutExceptionHandling();
        $response = $this->getJson('/stripe/balance/transactions')->assertSuccessful();
        $this->assertEquals('balance_transaction', $response['data']['data'][0]['object']);
    }
    /**@test*/
    public function test_address_is_required()
    {
        $this->withoutExceptionHandling();
        $jwtToken = $this->getauthJwtHeader();
        $data = self::SHOPPING_INFO;
        $data['address'] = '';
        $response = $this->postJson('cart/checkout', $data, $jwtToken);
        $response->assertStatus(400);
        $this->assertNotNull($response['data']['address']);
    }
    /**@test*/
    public function test_address_zip_code_is_required()
    {
        $this->withoutExceptionHandling();
        $jwtToken = $this->getauthJwtHeader();
        $data = self::SHOPPING_INFO;
        $data['postal_zip'] = '';
        $response = $this->postJson('cart/checkout', $data, $jwtToken);
        $response->assertStatus(400);
        $this->assertNotNull($response['data']['postal_zip']);
    }
    /**@test*/
    public function test_stripe_token_is_required()
    {
        $this->withoutExceptionHandling();
        $jwtToken = $this->getauthJwtHeader();
        $data = self::SHOPPING_INFO;
        $data['stripeToken'] = '';
        $response = $this->postJson('cart/checkout', $data, $jwtToken);
        $response->assertStatus(400);
        $this->assertNotNull($response['data']['stripeToken']);
    }
    /**@test*/
    public function test_cannot_buy_empty_cart()
    {
        $this->withoutExceptionHandling();
        $jwtToken = $this->getauthJwtHeader();
        $data = self::SHOPPING_INFO;
        $response = $this->postJson('cart/checkout', $data, $jwtToken);
        $response->assertStatus(400);
        $this->assertEquals('Please add items to your cart first!', $response['message']);
    }
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
    /**@test*/
    public function test_purchase_the_cart()
    {
        $this->withoutExceptionHandling();
        $response = $this->purchaseProduct();
        $response->assertSuccessful();
        $this->assertEquals('succeeded', $response['data']['status']);
    }
    /**@test*/
    public function test_can_retrieve_a_charge()
    {
        $this->withoutExceptionHandling();
        $response = $this->purchaseProduct();
        $chargeId = $response['data']['id'];
        $chargeResponse = $this->getJson('/charge/' . $chargeId)->assertSuccessful();
        $this->assertEquals(\auth()->guard('api')->user()->email, $chargeResponse['data']['receipt_email']);
    }
    /**@test*/
    public function test_data_saved_into_db_after_purchasing()
    {
        $this->withoutExceptionHandling();
        $this->assertDatabaseCount('charges', 0);
        $productResponse = $this->purchaseProduct()->assertSuccessful();
        $this->assertDatabaseCount('charges', 1);
        $this->assertEquals(Charge::select('charge_id')->first()->charge_id, $productResponse['data']['id']);
    }

    /**@test*/
    public function test_can_retrieve_all_charges()
    {
        $this->withoutExceptionHandling();
        $this->purchaseProduct();
        $chargeResponse = $this->getJson('/charge/all')->assertSuccessful();
        $this->assertEquals(\auth()->guard('api')->user()->email, $chargeResponse['data']['data'][0]['receipt_email']);
    }
}
