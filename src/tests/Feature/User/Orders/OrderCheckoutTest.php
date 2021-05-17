<?php

namespace Tests\Feature\User\Orders;

use App\Models\User;
use App\Models\Vendor;
use Carbon\Carbon;
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
    const STRIPE_ORDER_INFO = [
        'fullName' => 'name',
        'mobile' => '98392478',
        'postal_code' => '2342',
        'address' => 'test address',
        'shipping' => 'no ship',
        'paymentMethod' => 'stripe',
    ];
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
    public function testRecieve404WhenCheckoutAnotherUserOrder()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $authHeader = $this->getAuthJwtHeader($user);
        $this->createCart();
        $response = $this->postJson('api/' . $this->currentApiVersion . '/order', self::CUSTOMER_DATA, $authHeader);
        $this->postJson('api/' . $this->currentApiVersion . '/logout');
        $authHeader2 = $this->getAuthJwtHeader();
        $checkoutResponse = $this->postJson('api/' . $this->currentApiVersion . '/order/' . $response['data']['orderNumber'] . '/checkout', [], $authHeader2);
        $checkoutResponse->assertStatus(404);
    }

    // @test
    public function testOnlyAuthCanCheckout()
    {
        $response = $this->postJson('api/' . $this->currentApiVersion . '/order/orderId/checkout', []);
        $response->assertStatus(403);
    }

    // @test
    public function testCannotCheckoutNonExistingOrder()
    {
        $this->withoutExceptionHandling();
        $authHeader = $this->getAuthJwtHeader();
        $response = $this->postJson('api/' . $this->currentApiVersion . '/order/orderId/checkout', [], $authHeader);
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
        $response = $this->postJson('api/' . $this->currentApiVersion . '/order', $data, $authHeader);
        $this->postJson('api/' . $this->currentApiVersion . '/order/' . $response['data']['orderNumber'] . '/checkout', ['stipeToken' => 'tok_visa'], $authHeader);
        $checkoutResponse = $this->postJson('api/' . $this->currentApiVersion . '/order/' . $response['data']['orderNumber'] . '/checkout', ['stipeToken' => 'tok_visa'], $authHeader);
        $checkoutResponse->assertStatus(404);
    }

    // @test
    public function testCanPayWithPayPal()
    {
        $this->withoutExceptionHandling();
        $this->getAuthJwtHeader();
        $this->createCart();
        $this->createCart();
        $order = $this->postJson('api/' . $this->currentApiVersion . '/order', self::CUSTOMER_DATA)->assertStatus(302);
        $response = $this->postJson('api/' . $this->currentApiVersion . '/order/' . $order['data']['orderNumber'] . '/checkout');
        $response->assertRedirect();
        $this->assertEquals('https', \explode(':', $response['data'])[0]);
        $this->assertDatabaseCount('susbended_pay_pal_payments', 1);
    }

    // @test
    public function testCanPayWithStripe()
    {
        $this->withoutExceptionHandling();
        Event::fake();
        $this->actingAs(Vendor::factory()->create(['email_verified_at' => Carbon::now()]));
        $this->createCart();
        $this->createCart();
        $this->actingAs(User::factory()->create(['email_verified_at' => Carbon::now()]));
        $order = $this->postJson('api/' . $this->currentApiVersion . '/order', self::STRIPE_ORDER_INFO)->assertStatus(302);
        $response = $this->postJson('api/' . $this->currentApiVersion . '/order/' . $order['data']['orderNumber'] . '/checkout', ['stipeToken' => 'tok_visa']);
        $response->assertSuccessful();
    }
}
