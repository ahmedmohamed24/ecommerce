<?php

namespace Tests\Feature\User;

use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Carbon\Carbon;
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

    public function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['email_verified_at' => Carbon::now()]));
    }

    // @test
    public function testCanAddItemToCart()
    {
        $this->withoutExceptionHandling();
        for ($i = 0; $i < 2; ++$i) {
            $response = $this->createCart();
        }
        $response->assertSuccessful()->assertJsonFragment(['message' => 'success']);
        $cartContent = $this->getJson('api/'.$this->currentApiVersion.'/cart')->assertSuccessful();
        $this->assertCount(2, $cartContent['data']);
    }

    // @test
    public function testAddingProductShouldBeExistInTable()
    {
        $this->withoutExceptionHandling();
        $product = Product::factory()->make();
        $response = $this->postJson('api/'.$this->currentApiVersion.'/cart', $product->toArray());
        $response->assertStatus(406);
        $this->assertNotNull($response['data']['slug']);
    }

    // @test
    public function testAddingSameItemIncreasesQuantity()
    {
        $this->withoutExceptionHandling();
        $product = Product::factory()->create(['owner' => auth('vendor')->check() ? \auth()->id() : (Vendor::factory()->create())->id]);
        $response = $this->postJson('api/'.$this->currentApiVersion.'/cart', $product->toArray())->assertSuccessful();
        $rowId = $response['data']['rowId'];
        $this->assertEquals(1, $response['data']['qty']);
        $this->postJson('api/'.$this->currentApiVersion.'/cart', $product->toArray())->assertStatus(200);
        $this->postJson('api/'.$this->currentApiVersion.'/cart', $product->toArray())->assertStatus(200);
        $response1 = $this->getJson('api/'.$this->currentApiVersion.'/cart');
        $response1->assertSuccessful();
        $this->assertEquals(3, $response1['data']['items'][$rowId]['qty']);
    }

    public function testCanEmptyCart()
    {
        $this->withoutExceptionHandling();
        $this->createCart();
        $this->getJson('api/'.$this->currentApiVersion.'/cart/empty')->assertSuccessful();
        $response = $this->getJson('api/'.$this->currentApiVersion.'/cart');
        $this->assertCount(0, $response['data']['items']);
    }

    public function testCanRemoveItemFromCart()
    {
        $this->withoutExceptionHandling();
        $response = $this->createCart();
        $row_id = \array_values($response['data'])[0];
        $this->postJson('api/'.$this->currentApiVersion.'/cart/remove', ['rowId' => $row_id])->assertSuccessful();
        $response1 = $this->getJson('api/'.$this->currentApiVersion.'/cart');
        $this->assertCount(0, $response1['data']['items']);
    }

    public function testCanNotRemoveItemNotExistFromCart()
    {
        $this->withoutExceptionHandling();
        $row_id = 'anyTestValue';
        $response = $this->postJson('api/'.$this->currentApiVersion.'/cart/remove', ['rowId' => $row_id]);
        $response->assertStatus(404);
    }

    // @test
    public function testTotalPriceReturnedInCartPage()
    {
        $this->withoutExceptionHandling();
        for ($i = 0; $i < 3; ++$i) {
            $this->createCart();
        }
        $response1 = $this->getJson('api/'.$this->currentApiVersion.'/cart')->assertSuccessful();
        $this->assertNotNull($response1['data']['sub total']);
    }

    // @test
    public function testCanGetNumOfItemsPerCart()
    {
        $this->withoutExceptionHandling();
        for ($i = 0; $i < 3; ++$i) {
            $this->createCart();
        }
        $response1 = $this->getJson('api/'.$this->currentApiVersion.'/cart/count')->assertSuccessful();
        $this->assertEquals(3, $response1['data']);
    }

    // @test
    public function testOnlyAuthUsersCanCreateCart()
    {
        $this->actingAs(Vendor::factory()->create(['email_verified_at' => Carbon::now()]), 'vendor');
        $product = $this->attachCategories(Product::factory()->raw());
        $this->postJson('api/'.$this->currentApiVersion.'/product', $product)->assertSuccessful();
        $this->refreshApplication(); //logout current user
        $this->postJson('api/'.$this->currentApiVersion.'/cart', ['slug' => $product['slug']])->assertForbidden();
    }
}
