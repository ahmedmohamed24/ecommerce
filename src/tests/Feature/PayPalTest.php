<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Log;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class PayPalTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    const ORDERINFO = [
        'fullName' => 'name',
        'mobile' => '98392478',
        'postal_code' => '2342',
        'address' => 'ajfkal  afjhd',
        'shipping' => 'no ship',
        'paymentMethod' => 'paypal',
    ];

    // @test
    public function testCanPayWithPaypal()
    {
        $this->withoutExceptionHandling();
        $this->getauthJwtHeader();
        $this->createCart();
        $this->createCart();
        $order = $this->postJson('order', self::ORDERINFO)->assertStatus(302);
        $response = $this->postJson('/order/'.$order['data']['orderNumber'].'/checkout');
        $response->assertRedirect();
        $this->assertEquals('https', \explode(':', $response['data'])[0]);
        $this->assertDatabaseCount('susbended_pay_pal_payments', 1);
        // Log::notice($response['data']);
    }
}
