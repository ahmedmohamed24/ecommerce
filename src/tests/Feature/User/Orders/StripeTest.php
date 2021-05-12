<?php

namespace Tests\Feature\User\Orders;

use App\Models\User;
use App\Models\Vendor;
use Carbon\Carbon;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use function PHPUnit\Framework\assertIsNumeric;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class StripeTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;
    const ORDER_INFO = [
        'fullName' => 'name',
        'mobile' => '98392478',
        'postal_code' => '2342',
        'address' => 'test address',
        'shipping' => 'no ship',
        'paymentMethod' => 'stripe',
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['email_verified_at' => Carbon::now()]));
    }

    // @test
    public function testCanGetAccountBalance()
    {
        $this->withoutExceptionHandling();
        $response = $this->getJson('/stripe/balance')->assertSuccessful();
        assertIsNumeric($response['data']['available'][0]['amount']);
    }

    // @test
    public function testCanGetBalanceTransactions()
    {
        $this->withoutExceptionHandling();
        $response = $this->getJson('/stripe/balance/transactions')->assertSuccessful();
        $this->assertEquals('balance_transaction', $response['data']['data'][0]['object']);
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
        $order = $this->postJson('order', self::ORDER_INFO)->assertStatus(302);
        $response = $this->postJson('/order/'.$order['data']['orderNumber'].'/checkout', ['stipeToken' => 'tok_visa']);
        $response->assertSuccessful();
    }
}