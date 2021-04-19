<?php

namespace Tests\Feature\Product;

use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ProductValidationTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testProductNameShouldBeUnique()
    {
        $this->withoutExceptionHandling();
        $this->actingAs(User::factory()->create(['email_verified_at' => Carbon::now()]));
        Product::factory()->create(['name' => 'this is a test']);
        $product = Product::factory()->raw(['name' => 'this is a test']);
        $this->postJson('/product/', $product)->assertStatus(406);
        $this->assertDatabaseCount('products', 1);
    }

    // @test
    public function testProductNameIsRequired()
    {
        $this->withoutExceptionHandling();
        $this->actingAs(User::factory()->create(['email_verified_at' => Carbon::now()]));
        $product = Product::factory()->raw(['name' => '']);
        $response = $this->postJson('/product/', $product);
        $response->assertStatus(406);
        $response->assertJsonFragment(['name' => ['The name must be a string.', 'The name field is required.']]);
    }

    // @test
    public function testProductDescriptionIsRequired()
    {
        $this->withoutExceptionHandling();
        $this->actingAs(User::factory()->create(['email_verified_at' => Carbon::now()]));
        $product = Product::factory()->raw(['description' => '']);
        $response = $this->postJson('/product/', $product);
        $response->assertStatus(406);
        $response->assertJsonFragment(['description' => ['The description field is required.']]);
    }

    // @test
    public function testProductDetailsIsRequired()
    {
        $this->withoutExceptionHandling();
        $this->actingAs(User::factory()->create(['email_verified_at' => Carbon::now()]));
        $product = Product::factory()->raw(['details' => '']);
        $response = $this->postJson('/product/', $product);
        $response->assertStatus(406);
        $response->assertJsonFragment(['details' => ['The details field is required.']]);
    }

    // @test
    public function testProductPriceIsRequired()
    {
        $this->withoutExceptionHandling();
        $this->actingAs(User::factory()->create(['email_verified_at' => Carbon::now()]));
        $product = Product::factory()->raw(['price' => '']);
        $response = $this->postJson('/product/', $product);
        $response->assertStatus(406);
        $response->assertJsonFragment(['price' => ['The price field is required.']]);
    }

    // @test
    public function testProductPriceMustBeNumeric()
    {
        $this->withoutExceptionHandling();
        $this->actingAs(User::factory()->create(['email_verified_at' => Carbon::now()]));
        $product = Product::factory()->raw(['price' => 'test']);
        $response = $this->postJson('/product/', $product);
        $response->assertStatus(406);
        $response->assertJsonFragment(['price' => ['The price must be a number.']]);
    }

    // @test
    public function testProductPriceMustBeMoreThan0()
    {
        $this->withoutExceptionHandling();
        $this->actingAs(User::factory()->create(['email_verified_at' => Carbon::now()]));
        $product = Product::factory()->raw(['price' => '-10']);
        $response = $this->postJson('/product/', $product);
        $response->assertStatus(406);
        $response->assertJsonFragment(['price' => ['The price must be at least 1.']]);
    }

    public function testProductMustHaveCategories()
    {
        $this->withoutExceptionHandling();
        $this->actingAs(User::factory()->create(['email_verified_at' => Carbon::now()]));
        $product = Product::factory()->raw();
        $response = $this->postJson('/product/', $product);
        $response->assertStatus(406);
        $response->assertJsonFragment(['categories' => ['The categories field is required.']]);
    }

    // @test
    public function testCannotRestoreNonDeletedProduct()
    {
        $this->withoutExceptionHandling();
        $this->actingAs(User::factory()->create(['email_verified_at' => Carbon::now()]));
        $productModel = Product::create($product = Product::factory()->raw());
        $this->json('POST', $productModel->path().'/restore', $product)->assertStatus(404);
    }

    // @test
    public function testVisitingNonExistProductReturns404()
    {
        $this->withoutExceptionHandling();
        $this->actingAs(User::factory()->create(['email_verified_at' => Carbon::now()]));
        $product = Product::factory()->make();
        $this->get($product->path())->assertStatus(404);
    }
}
