<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CartTest extends TestCase
{
    use WithFaker,RefreshDatabase;
    /**@test*/
    public function test_can_add_item_to_cart()
    {
        $this->withoutExceptionHandling();
        $product=Product::factory()->create();
        $product1=Product::factory()->create();
        $this->postJson('/cart', $product->toArray());
        $response=$this->postJson('/cart', $product1->toArray());
        $response->assertSuccessful()->assertJsonFragment(['message'=>'success']);
        $cartContent=$this->getJson('/cart')->assertSuccessful();
        $this->assertCount(2, $cartContent['data']);
    }
    /**@test*/
    public function test_adding_product_should_be_exist_in_table()
    {
        $this->withoutExceptionHandling();
        $product=Product::factory()->make(['id'=>1]);
        $response= $this->postJson('/cart', $product->toArray());
        $response->assertStatus(406);
        $this->assertNotNull($response['data']['slug']);
    }
    /**@test*/
    public function test_adding_same_item_increases_quantity()
    {
        $this->withoutExceptionHandling();
        $product=Product::factory()->create();
        $response=$this->postJson('/cart', $product->toArray())->assertSuccessful();
        $rowId=$response['data']['rowId'];
        $this->assertEquals(1, $response['data']['qty']);
        $this->postJson('/cart', $product->toArray())->assertStatus(200);
        $response1= $this->getJson('/cart');
        $response1->assertSuccessful();
        $this->assertEquals(2, $response1['data']['items'][$rowId]['qty']);
    }
    public function test_can_empty_cart()
    {
        $this->withoutExceptionHandling();
        $product=Product::factory()->create();
        $this->postJson('/cart', $product->toArray())->assertSuccessful();
        $this->getJson('/cart/empty')->assertSuccessful();
        $response=$this->getJson('/cart');
        $this->assertCount(0, $response['data']['items']);
    }
    public function test_can_remove_item_from_cart()
    {
        $this->withoutExceptionHandling();
        $product=Product::factory()->create();
        $response=$this->postJson('/cart', $product->toArray())->assertSuccessful();
        $row_id=\array_values($response['data'])[0];
        $this->postJson('/cart/remove', ['rowId'=>$row_id])->assertSuccessful();
        $response1=$this->getJson('/cart');
        $this->assertCount(0, $response1['data']['items']);
    }
    public function test_can_not_remove_item_not_exist_from_cart()
    {
        $this->withoutExceptionHandling();
        $row_id='anyTestValue';
        $response=$this->postJson('/cart/remove', ['rowId'=>$row_id]);
        $response->assertStatus(404);
    }
    /**@test*/
    public function test_total_price_returned_in_cart_page()
    {
        $this->withoutExceptionHandling();
        $product1=Product::factory()->create();
        $product2=Product::factory()->create();
        $product3=Product::factory()->create();
        $this->postJson('/cart', $product1->toArray())->assertSuccessful();
        $this->postJson('/cart', $product2->toArray())->assertSuccessful();
        $this->postJson('/cart', $product3->toArray())->assertSuccessful();
        $response1=$this->getJson('/cart')->assertSuccessful();
        $this->assertNotNull($response1['data']['sub total']);
    }
    /**@test*/
    public function test_can_get_num_of_items_per_cart()
    {
        $this->withoutExceptionHandling();
        $product1=Product::factory()->create();
        $product2=Product::factory()->create();
        $product3=Product::factory()->create();
        $this->postJson('/cart', $product1->toArray())->assertSuccessful();
        $this->postJson('/cart', $product2->toArray())->assertSuccessful();
        $this->postJson('/cart', $product3->toArray())->assertSuccessful();
        $response1=$this->getJson('/cart/count')->assertSuccessful();
        $this->assertEquals(3, $response1['data']);
    }
}
