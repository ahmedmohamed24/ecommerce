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
        $response = $this->postJson('order/checkout');
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
