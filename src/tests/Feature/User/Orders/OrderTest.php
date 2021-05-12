<?php

namespace Tests\Feature\User\Orders;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class OrderTest extends TestCase
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

    // @test
    public function testCanMakeOrder()
    {
        $this->withoutExceptionHandling();
        $authHeader = $this->getAuthJwtHeader();
        $this->createCart();
        $response = $this->postJson('/order', self::CUSTOMER_DATA, $authHeader);
        $response->assertStatus(302);
        $this->assertDatabaseCount('orders', 1);
    }

    // @test
    public function testCannotMakeOrderToEmptyCart()
    {
        $this->withoutExceptionHandling();
        $authHeader = $this->getAuthJwtHeader();
        $response = $this->postJson('/order', self::CUSTOMER_DATA, $authHeader);
        $response->assertStatus(400);
        $this->assertEquals('Cart is Empty', $response['message']);
    }

    public function testAddressIsRequired()
    {
        $this->withoutExceptionHandling();
        $jwtToken = $this->getAuthJwtHeader();
        $data = self::CUSTOMER_DATA;
        $data['address'] = '';
        $response = $this->postJson('/order', $data, $jwtToken);
        $response->assertStatus(400);
        $this->assertNotNull($response['errors']['address']);
    }

    // @test
    public function testAddressZipCodeIsRequired()
    {
        $this->withoutExceptionHandling();
        $jwtToken = $this->getAuthJwtHeader();
        $data = self::CUSTOMER_DATA;
        $data['postal_code'] = '';
        $this->createCart();
        $response = $this->postJson('/order', $data, $jwtToken);
        $response->assertStatus(400);
        $this->assertNotNull($response['errors']['postal_code']);
    }

    // @test
    public function testPaymentMethodIsRequired()
    {
        $this->withoutExceptionHandling();
        $jwtToken = $this->getAuthJwtHeader();
        $data = self::CUSTOMER_DATA;
        $data['paymentMethod'] = '';
        $this->createCart();
        $response = $this->postJson('/order', $data, $jwtToken);
        $response->assertStatus(400);
        $this->assertNotNull($response['errors']['paymentMethod']);
    }

    // @test
    public function testCannotBuyEmptyCart()
    {
        $this->withoutExceptionHandling();
        $jwtToken = $this->getAuthJwtHeader();
        $data = self::CUSTOMER_DATA;
        $response = $this->postJson('/order', $data, $jwtToken);
        $response->assertStatus(400);
        $this->assertEquals('Cart is Empty', $response['message']);
    }
}
