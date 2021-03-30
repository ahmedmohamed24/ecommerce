<?php

namespace Tests\Feature;

use App\Models\Charge;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class CheckoutTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    // @test
    public function testAddressIsRequired()
    {
        $this->withoutExceptionHandling();
        $jwtToken = $this->getauthJwtHeader();
        $data = self::SHOPPING_INFO;
        $data['address'] = '';
        $response = $this->postJson('cart/checkout', $data, $jwtToken);
        $response->assertStatus(400);
        $this->assertNotNull($response['data']['address']);
    }

    // @test
    public function testAddressZipCodeIsRequired()
    {
        $this->withoutExceptionHandling();
        $jwtToken = $this->getauthJwtHeader();
        $data = self::SHOPPING_INFO;
        $data['postal_zip'] = '';
        $response = $this->postJson('cart/checkout', $data, $jwtToken);
        $response->assertStatus(400);
        $this->assertNotNull($response['data']['postal_zip']);
    }

    // @test
    public function testStripeTokenIsRequired()
    {
        $this->withoutExceptionHandling();
        $jwtToken = $this->getauthJwtHeader();
        $data = self::SHOPPING_INFO;
        $data['stripeToken'] = '';
        $response = $this->postJson('cart/checkout', $data, $jwtToken);
        $response->assertStatus(400);
        $this->assertNotNull($response['data']['stripeToken']);
    }

    // @test
    public function testCannotBuyEmptyCart()
    {
        $this->withoutExceptionHandling();
        $jwtToken = $this->getauthJwtHeader();
        $data = self::SHOPPING_INFO;
        $response = $this->postJson('cart/checkout', $data, $jwtToken);
        $response->assertStatus(400);
        $this->assertEquals('Please add items to your cart first!', $response['message']);
    }

    // @test
    public function testPurchaseTheCart()
    {
        $this->withoutExceptionHandling();
        $response = $this->purchaseProduct();
        $response->assertSuccessful();
        $this->assertEquals('succeeded', $response['data']['status']);
    }

    // @test
    public function testDataSavedIntoDbAfterPurchasing()
    {
        $this->withoutExceptionHandling();
        $this->assertDatabaseCount('charges', 0);
        $productResponse = $this->purchaseProduct()->assertSuccessful();
        $this->assertDatabaseCount('charges', 1);
        $this->assertEquals(Charge::select('charge_id')->first()->charge_id, $productResponse['data']['id']);
    }
}
