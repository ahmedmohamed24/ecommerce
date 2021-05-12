<?php

namespace Tests\Feature\User\Orders;

use App\Models\User;
use Event;
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
    const CUSTOMER_DATA = [
        'fullName' => 'ahmed mohamed',
        'mobile' => '023892477',
        'postal_code' => '23443',
        'address' => 'test address',
        'shipping' => 'no shipping',
        'paymentMethod' => 'paypal',
    ];

    public function setup(): void
    {
        parent::setUp();
        Event::fake();
    }

    // @test
    public function testOnlyOrderOwnerCanCheckout()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $authHeader = $this->getAuthJwtHeader($user);
        $this->createCart();
        $response = $this->postJson('/order', self::CUSTOMER_DATA, $authHeader);
        $this->postJson('/logout');
        $authHeader2 = $this->getAuthJwtHeader();
        $checkoutResponse = $this->postJson('order/'.$response['data']['orderNumber'].'/checkout', [], $authHeader2);
        $checkoutResponse->assertStatus(404);
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
        $authHeader = $this->getAuthJwtHeader();
        $response = $this->postJson('order/orderId/checkout', [], $authHeader);
        $response->assertStatus(404);
        $this->assertEquals('Not Found', $response['message']);
    }

    // @test
    public function testOrderCheckoutOccursOnceIfPaid()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $authHeader = $this->getAuthJwtHeader($user);
        $this->createCart();
        //change payment method
        $data = self::CUSTOMER_DATA;
        $data['paymentMethod'] = 'stripe';
        $response = $this->postJson('/order', $data, $authHeader);
        $this->postJson('order/'.$response['data']['orderNumber'].'/checkout', ['stipeToken' => 'tok_visa'], $authHeader);
        $checkoutResponse = $this->postJson('order/'.$response['data']['orderNumber'].'/checkout', ['stipeToken' => 'tok_visa'], $authHeader);
        $checkoutResponse->assertStatus(404);
    }
}
