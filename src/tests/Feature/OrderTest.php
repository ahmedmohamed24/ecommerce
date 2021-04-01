<?php

namespace Tests\Feature;

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
    const CUSTOMERDATA = [
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
        $authHeader = $this->getauthJwtHeader();
        $this->createCart();
        $response = $this->postJson('/order', self::CUSTOMERDATA, $authHeader);
        $response->assertStatus(302);
        $this->assertDatabaseCount('orders', 1);
    }

    // @test
    public function testCannotMakeOrderToEmptyCart()
    {
        $this->withoutExceptionHandling();
        $authHeader = $this->getauthJwtHeader();
        $response = $this->postJson('/order', self::CUSTOMERDATA, $authHeader);
        $response->assertStatus(400);
        $this->assertEquals('Cart is Empty', $response['message']);
    }

    public function testAddressIsRequired()
    {
        $this->withoutExceptionHandling();
        $jwtToken = $this->getauthJwtHeader();
        $data = self::CUSTOMERDATA;
        $data['address'] = '';
        $response = $this->postJson('/order', $data, $jwtToken);
        $response->assertStatus(400);
        $this->assertNotNull($response['errors']['address']);
    }

    // @test
    public function testAddressZipCodeIsRequired()
    {
        $this->withoutExceptionHandling();
        $jwtToken = $this->getauthJwtHeader();
        $data = self::CUSTOMERDATA;
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
        $jwtToken = $this->getauthJwtHeader();
        $data = self::CUSTOMERDATA;
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
        $jwtToken = $this->getauthJwtHeader();
        $data = self::CUSTOMERDATA;
        $response = $this->postJson('/order', $data, $jwtToken);
        $response->assertStatus(400);
        $this->assertEquals('Cart is Empty', $response['message']);
    }

    // @test
    public function testOnlyAuthCanOrder()
    {
        $this->createCart();
        $response = $this->postJson('/order', self::CUSTOMERDATA);
        $response->assertStatus(403);
    }
}
