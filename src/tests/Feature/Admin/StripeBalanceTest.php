<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class StripeBalanceTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->actingAs(Admin::factory()->create(), 'admin');
    }

    // @test
    public function testCanGetAccountBalance()
    {
        $this->withoutExceptionHandling();
        $response = $this->getJson('/stripe/balance')->assertSuccessful();
        $this->assertIsNumeric($response['data']['available'][0]['amount']);
    }

    // @test
    public function testCanGetBalanceTransactions()
    {
        $this->withoutExceptionHandling();
        $response = $this->getJson('/stripe/balance/transactions')->assertSuccessful();
        $this->assertIsArray($response['data']['data']);
    }

    // @test
    public function testCanGetAllCharges()
    {
        $this->withoutExceptionHandling();
        $response = $this->getJson('/stripe/charge/all')->assertSuccessful();
        $this->assertIsArray($response['data']['data']);
    }

    // @test
    public function testCanGetSpecificCharges()
    {
        $this->withoutExceptionHandling();
        $response = $this->getJson('/stripe/charge/ch_1Iqir4AfhLnpMqzdJ7VGkX1M')->assertSuccessful();
        $this->assertEquals('ch_1Iqir4AfhLnpMqzdJ7VGkX1M', $response['data']['id']);
    }
}
