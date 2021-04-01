<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class CartTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    // @test
    public function testCanAddItemToCart()
    {
        $this->withoutExceptionHandling();
        for ($i = 0; $i < 2; ++$i) {
            $response = $this->createCart();
        }
        $response->assertSuccessful()->assertJsonFragment(['message' => 'success']);
        $cartContent = $this->getJson('/cart')->assertSuccessful();
        $this->assertCount(2, $cartContent['data']);
    }

    // @test
    public function testAddingProductShouldBeExistInTable()
    {
        $this->withoutExceptionHandling();
        $product = Product::factory()->make();
        $response = $this->postJson('/cart', $product->toArray());
        $response->assertStatus(406);
        $this->assertNotNull($response['data']['slug']);
    }

    // @test
    public function testAddingSameItemIncreasesQuantity()
    {
        $this->withoutExceptionHandling();
        $product = Product::factory()->create();
        $response = $this->postJson('/cart', $product->toArray())->assertSuccessful();
        $rowId = $response['data']['rowId'];
        $this->assertEquals(1, $response['data']['qty']);
        $this->postJson('/cart', $product->toArray())->assertStatus(200);
        $response1 = $this->getJson('/cart');
        $response1->assertSuccessful();
        $this->assertEquals(2, $response1['data']['items'][$rowId]['qty']);
    }

    public function testCanEmptyCart()
    {
        $this->withoutExceptionHandling();
        $this->createCart();
        $this->getJson('/cart/empty')->assertSuccessful();
        $response = $this->getJson('/cart');
        $this->assertCount(0, $response['data']['items']);
    }

    public function testCanRemoveItemFromCart()
    {
        $this->withoutExceptionHandling();
        $response = $this->createCart();
        $row_id = \array_values($response['data'])[0];
        $this->postJson('/cart/remove', ['rowId' => $row_id])->assertSuccessful();
        $response1 = $this->getJson('/cart');
        $this->assertCount(0, $response1['data']['items']);
    }

    public function testCanNotRemoveItemNotExistFromCart()
    {
        $this->withoutExceptionHandling();
        $row_id = 'anyTestValue';
        $response = $this->postJson('/cart/remove', ['rowId' => $row_id]);
        $response->assertStatus(404);
    }

    // @test
    public function testTotalPriceReturnedInCartPage()
    {
        $this->withoutExceptionHandling();
        for ($i = 0; $i < 3; ++$i) {
            $this->createCart();
        }
        $response1 = $this->getJson('/cart')->assertSuccessful();
        $this->assertNotNull($response1['data']['sub total']);
    }

    // @test
    public function testCanGetNumOfItemsPerCart()
    {
        $this->withoutExceptionHandling();
        for ($i = 0; $i < 3; ++$i) {
            $this->createCart();
        }
        $response1 = $this->getJson('/cart/count')->assertSuccessful();
        $this->assertEquals(3, $response1['data']);
    }
}
