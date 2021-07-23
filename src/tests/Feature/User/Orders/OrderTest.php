<?php

namespace Tests\Feature\User\Orders;

use App\Models\Product;
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
        'cart' => [
            ['product' => 'product1', 'stock' => 1],
            ['product' => 'product1', 'stock' => 2],
        ],
    ];

    public function setUp(): void
    {
        parent::setUp();
        Product::factory()->create(['slug' => 'product1']);
    }

    // @test
    public function testCanMakeOrder()
    {
        $this->withoutExceptionHandling();
        $authHeader = $this->getAuthJwtHeader();
        $response = $this->postJson('api/'.$this->currentApiVersion.'/order', self::CUSTOMER_DATA, $authHeader);
        $response->assertStatus(302);
        $this->assertDatabaseCount('orders', 1);
    }

    // @test
    public function testCannotMakeOrderToEmptyCart()
    {
        $this->withoutExceptionHandling();
        $authHeader = $this->getAuthJwtHeader();
        $data = self::CUSTOMER_DATA;
        $data['cart'] = [];
        $response = $this->postJson('api/'.$this->currentApiVersion.'/order', $data, $authHeader);
        $response->assertStatus(406);
        $response->assertJsonValidationErrors('cart');
    }

    public function testAddressIsRequired()
    {
        $this->withoutExceptionHandling();
        $jwtToken = $this->getAuthJwtHeader();
        $data = self::CUSTOMER_DATA;
        $data['address'] = '';
        $response = $this->postJson('api/'.$this->currentApiVersion.'/order', $data, $jwtToken);
        $response->assertStatus(406);
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
        $response = $this->postJson('api/'.$this->currentApiVersion.'/order', $data, $jwtToken);
        $response->assertStatus(406);
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
        $response = $this->postJson('api/'.$this->currentApiVersion.'/order', $data, $jwtToken);
        $response->assertStatus(406);
        $this->assertNotNull($response['errors']['paymentMethod']);
    }
}
