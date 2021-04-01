<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class PayPalTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    // @test
    public function testCanPayWithPaypal()
    {
        $this->withoutExceptionHandling();
        $this->createCart();
        $this->createCart();
        $data = [
            'fullName' => 'name',
            'mobile' => '98392478',
            'postal_code' => '2342',
            'address' => 'ajfkal  afjhd',
            'shipping' => 'no ship',
            'paymentMethod' => 'paypal',
        ];
        $this->getauthJwtHeader();
        $order = $this->postJson('order', $data)->assertStatus(302);
        $response = $this->postJson('/order/'.$order['data']['orderNumber'].'/checkout');
        // dd($response);
        $response->assertSuccessful();
        $link = null;
        foreach ($response['data']['result']['links'] as $link) {
            if ('approve' === $link['rel']) {
                $link = true;

                break;
            }
        }
        $this->assertTrue($link);
    }
}
