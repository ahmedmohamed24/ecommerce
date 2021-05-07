<?php

namespace Tests\Feature\User;

use App\Models\Product;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ProductTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function setup(): void
    {
        parent::setUp();
        $this->actingAs(Vendor::factory()->create(['email_verified_at' => Carbon::now()]));
    }

    // @test
    public function testCanPaginateProducts()
    {
        $this->withoutExceptionHandling();
        Product::factory(100)->create();
        $this->getJson('/product?page=2')->assertStatus(200)->assertJsonFragment(['current_page' => 2]);
    }

    public function testPriceIsInMoneyFormat()
    {
        $product = Product::factory()->create(['price' => 522.232]);
        $this->assertEquals($product->formattedPrice(), '$522.23');
    }

    // @test
    public function testReturnProductsInRandomOrder()
    {
        $this->withExceptionHandling();
        Product::factory(100)->create();
        $this->getJson('/product/random')->assertStatus(200)->assertJsonFragment(['message' => 'success']);
    }
}
