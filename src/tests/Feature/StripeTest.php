<?php

namespace Tests\Feature;

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
    // public function testCanRetrieveACharge()
    // {
    //     $this->withoutExceptionHandling();
    //     $response = $this->purchaseProduct();
    //     dd($response);
    //     $chargeId = $response['data']['id'];
    //     $chargeResponse = $this->getJson('/charge/'.$chargeId)->assertSuccessful();
    //     $this->assertEquals(\auth()->guard('api')->user()->email, $chargeResponse['data']['receipt_email']);
    // }

    // // @test
    // public function testCanRetrieveAllCharges()
    // {
    //     $this->withoutExceptionHandling();
    //     $this->purchaseProduct();
    //     $chargeResponse = $this->getJson('/charge/all')->assertSuccessful();
    //     $this->assertEquals(\auth()->guard('api')->user()->email, $chargeResponse['data']['data'][0]['receipt_email']);
    // }
}
