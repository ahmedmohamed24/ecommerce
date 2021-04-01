<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class OrderCheckoutTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;
    const CUSTOMERDATA = [
        'fullName' => 'ahmed mohamed',
        'mobile' => '023892477',
        'postal_code' => '23443',
        'address' => 'test address',
        'shipping' => 'no shipping',
        'paymentMethod' => 'paypal',
    ];

    // @test
    public function testCanCheckoutOrderUsingPaypal()
    {
        $this->withoutExceptionHandling();
        $authHeader = $this->getauthJwtHeader();
        $this->createCart();
        $response = $this->postJson('/order', self::CUSTOMERDATA, $authHeader);
        $response2 = $this->postJson('order/'.$response['data']['orderNumber'].'/checkout', [], $authHeader);
        $response2->assertStatus(200);
    }

    // @test
    public function testCanCheckoutOrderUsingStripe()
    {
        $this->withoutExceptionHandling();
    }

    // @test
    public function testOnlyOrderOwnerCanCheckout()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $authHeader = $this->getauthJwtHeader($user);
        $this->createCart();
        $response = $this->postJson('/order', self::CUSTOMERDATA, $authHeader);
        $this->postJson('/logout');
        $authHeader2 = $this->getauthJwtHeader();
        $response2 = $this->postJson('order/'.$response['data']['orderNumber'].'/checkout', [], $authHeader2);
        $response2->assertStatus(404);
    }

    // @test
    public function testOnlyAuthCanCheckout()
    {
        $response = $this->postJson('order/orderId/checkout', []);
        $response->assertStatus(403);
    }

    // @test
    public function testCannotCheckoutNonExistingOrder()
    {
        $this->withoutExceptionHandling();
        $authHeader = $this->getauthJwtHeader();
        $response = $this->postJson('order/orderId/checkout', [], $authHeader);
        $response->assertStatus(404);
        $this->assertEquals('Not Found', $response['message']);
    }

    // @test
    public function testOrderCheckoutOccursOnce()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $authHeader = $this->getauthJwtHeader($user);
        $this->createCart();
        $response = $this->postJson('/order', self::CUSTOMERDATA, $authHeader);
        $this->postJson('order/'.$response['data']['orderNumber'].'/checkout', [], $authHeader);
        $response2 = $this->postJson('order/'.$response['data']['orderNumber'].'/checkout', [], $authHeader);
        $response2->assertStatus(404);
    }

    // @test
    public function testEmailIsSentToUserAfterCheckout()
    {
        $this->withoutExceptionHandling();
    }
}
