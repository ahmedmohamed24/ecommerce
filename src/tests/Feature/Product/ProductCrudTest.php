<?php

namespace Tests\Feature\Product;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ProductCrudTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    // @test
    public function testStatus201ResponseWhenCreatingProduct()
    {
        $this->withoutExceptionHandling();
        $this->actingAs(User::factory()->create(['email_verified_at' => Carbon::now()]));
        $product = Product::factory()->raw();
        $product = $this->attachCategories($product);
        $response = $this->postJson('/product/', $product);
        $response->assertStatus(201);
    }

    // @test
    public function testHasOneRowInProductsTableWhenCreatingProduct()
    {
        $this->withoutExceptionHandling();
        $this->actingAs(User::factory()->create(['email_verified_at' => Carbon::now()]));
        $product = Product::factory()->raw();
        $product = $this->attachCategories($product);
        $this->postJson('/product/', $product);
        $this->assertDatabaseCount('products', 1);
    }

    // @test
    public function testResponse200WhenVisitShowProductUsingSlug()
    {
        $this->withoutExceptionHandling();
        $this->actingAs(User::factory()->create(['email_verified_at' => Carbon::now()]));
        $product = Product::factory()->raw();
        $product = $this->attachCategories($product);
        $this->postJson('/product', $product);
        $this->get(Product::firstOrFail()->path())->assertStatus(200);
    }

    // @test
    public function testResponseContainsNameWhenVisitShowProductUsingSlug()
    {
        $this->withoutExceptionHandling();
        $this->actingAs(User::factory()->create(['email_verified_at' => Carbon::now()]));
        $product = Product::factory()->raw();
        $product = $this->attachCategories($product);
        $this->postJson('/product', $product);
        $response = $this->get(Product::firstOrFail()->path())->assertStatus(200);
        $this->assertEquals($product['name'], $response['data']['product']['name']);
    }

    // @test
    public function testCanShowRecommendedProductsBasedOnProductSelection()
    {
        $this->withoutExceptionHandling();
        $this->actingAs(User::factory()->create(['email_verified_at' => Carbon::now()]));
        Category::factory(3)->create();
        Product::factory(38)->create();
        DB::table('category_product')->insert(['product_slug' => Product::find(2)->slug, 'category_slug' => Category::find(1)->slug]);
        DB::table('category_product')->insert(['product_slug' => Product::find(3)->slug, 'category_slug' => Category::find(1)->slug]);
        DB::table('category_product')->insert(['product_slug' => Product::find(4)->slug, 'category_slug' => Category::find(1)->slug]);
        $product = Product::factory()->raw(['name' => 'identified']);
        $product['categories'] = [Category::find(1)->slug, Category::find(2)->slug, Category::find(3)->slug];
        $this->postJson('/product', $product);
        $product = Product::find(39);
        $jsonResponse = $this->getJson($product->path());
        $jsonResponse->assertStatus(200);
        $this->assertCount(3, $jsonResponse['data']['recommended_products']);
    }

    // @test
    public function testUpdateProductReturns200()
    {
        $this->withoutExceptionHandling();
        $this->actingAs(User::factory()->create(['email_verified_at' => Carbon::now()]));
        $product = Product::factory()->create();
        //add edits to product
        $product->name = 'test name';
        $Updatedproduct = $this->attachCategories($product->toArray());
        $response = $this->putJson($product->path(), $Updatedproduct);
        $response->assertStatus(200);
    }

    // @test
    public function testProductIsUpdatedInDBAfterUpdatingRequest()
    {
        $this->withoutExceptionHandling();
        $this->actingAs(User::factory()->create(['email_verified_at' => Carbon::now()]));
        $product = Product::factory()->create();
        //add edits to product
        $product->name = 'test name';
        $Updatedproduct = $this->attachCategories($product->toArray());
        $this->putJson($product->path(), $Updatedproduct);
        unset($Updatedproduct['categories']);
        $this->assertDatabaseHas('products', ['name' => $Updatedproduct['name']]);
    }

    // @test
    public function testCanUpdateProductSlugWhichHasARelationToCategory()
    {
        $product = Product::factory()->raw();
        $this->actingAs(User::factory()->create(['email_verified_at' => Carbon::now()]));
        $product = $this->attachCategories($product);
        $product = $this->attachCategories($product);
        $oldProduct = $this->postJson('product', $product)->assertSuccessful();
        $product['name'] = 'new name';
        $response = $this->putJson('product/'.$oldProduct['data']['slug'], $product);
        $response->assertSuccessful();
        $this->assertEquals($product['name'], $response['data']['name']);
    }

    // @test
    public function testCanUpdateCategorySlugWhichHasARelationToProducts()
    {
        $category = Category::factory()->create();
        $this->actingAs(User::factory()->create(['email_verified_at' => Carbon::now()]));
        $product1 = Product::factory()->raw();
        $product2 = Product::factory()->raw();
        $product1['categories'] = $product2['categories'] = [$category->slug];
        $this->postJson('product', $product1)->assertSuccessful();
        $this->postJson('product', $product2)->assertSuccessful();
        $category->name = 'new name';
        $response = $this->putJson($category->path(), $category->toArray());
        $response->assertSuccessful();
        $this->assertEquals($category->name, $response['data']['name']);
        $products = Category::where('slug', $response['data']['slug'])->first()->products;
        $this->assertEquals($product1['slug'], $products[0]['slug']);
    }

    // @test
    public function testCanSoftDeleteProduct()
    {
        $this->withoutExceptionHandling();
        $this->actingAs(User::factory()->create(['email_verified_at' => Carbon::now()]));
        $product = Product::factory()->create();
        $this->assertDatabaseCount('products', 1);
        $this->assertNull(Product::first()->deleted_at);
        $this->deleteJson($product->path())->assertStatus(200);
        $this->assertNotNull(Product::withTrashed()->first()->deleted_at);
        $this->assertDatabaseCount('products', 1);
    }

    // @test
    public function testCanRestoreProduct()
    {
        $this->withoutExceptionHandling();
        $this->actingAs(User::factory()->create(['email_verified_at' => Carbon::now()]));
        $productModel = Product::create($product = Product::factory()->raw());
        $this->json('DELETE', $productModel->path(), $product)->assertStatus(200);
        $this->assertNotNull(Product::withTrashed()->first()->deleted_at);
        $response = $this->json('POST', $productModel->path().'/restore', $product)->assertStatus(200);
        $this->assertNotNull($response['data']['name']);
        $this->assertNull(Product::withTrashed()->first()->deleted_at);
    }

    // @test
    public function testCanPaginateProducts()
    {
        $this->withoutExceptionHandling();
        $this->actingAs(User::factory()->create(['email_verified_at' => Carbon::now()]));
        Product::factory(100)->create();
        $this->getJson('/product?page=2')->assertStatus(200)->assertJsonFragment(['current_page' => 2]);
    }

    // @test
    public function testReturnProductsInRandomOrder()
    {
        $this->withExceptionHandling();
        $this->actingAs(User::factory()->create(['email_verified_at' => Carbon::now()]));
        Product::factory(100)->create();
        $this->getJson('/product/random')->assertStatus(200)->assertJsonFragment(['message' => 'success']);
    }

    // @test
    public function testReturnTrashedProducts()
    {
        $this->withExceptionHandling();
        $this->actingAs(User::factory()->create(['email_verified_at' => Carbon::now()]));
        Product::factory(100)->create();
        $this->deleteJson(Product::first()->path());
        $this->deleteJson(Product::first()->path());
        $reponse = $this->getJson('/product/trashed')->assertStatus(200);
        $this->assertCount(2, $reponse['data']['data']);
    }

    public function testPriceIsInMoneyFormat()
    {
        $product = Product::factory()->create(['price' => 522.232]);
        $this->assertEquals($product->formattedPrice(), '$522.23');
    }
}
