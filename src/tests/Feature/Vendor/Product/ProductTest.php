<?php

namespace Tests\Feature\Vendor\Product;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
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
        $this->actingAs(Vendor::factory()->create(['email_verified_at' => Carbon::now()]), 'vendor');
    }

    // @test
    public function testVendorGet200StatusWhenCreateProduct()
    {
        $this->withoutExceptionHandling();
        $product = $this->attachCategories(Product::factory()->raw());
        $response = $this->postJson('api/' . $this->currentApiVersion . '/product', $product);
        $response->assertSuccessful();
    }

    // @test
    public function testVisitingProductVendorReturn200Status()
    {
        $this->withoutExceptionHandling();
        $product = $this->attachCategories(Product::factory()->raw());
        $this->postJson('api/' . $this->currentApiVersion . '/product', $product);
        $response = $this->getJson("api/$this->currentApiVersion/product/{$product['slug']}/vendor");
        $response->assertSuccessful();
    }

    // @test
    public function testVisitingProductVendorReturnsVendorName()
    {
        $this->withoutExceptionHandling();
        $product = $this->attachCategories(Product::factory()->raw());
        $this->postJson('api/' . $this->currentApiVersion . '/product', $product);
        $response = $this->getJson('api/' . $this->currentApiVersion . '/product/' . $product['slug'] . '/vendor');
        self::assertEquals(auth()->user()->name, $response['data']['vendor']['name']);
    }

    // @test
    public function testVisitingProductVendorReturnsVendorMoreProduct()
    {
        $this->withoutExceptionHandling();
        Product::factory(3)->create();
        $response = $this->getJson('api/' . $this->currentApiVersion . '/product/' . Product::first()->slug . '/vendor');
        self::assertCount(3, $response['data']['products']);
    }

    // @test
    public function testStatus201ResponseWhenCreatingProduct()
    {
        $this->withoutExceptionHandling();
        $product = Product::factory()->raw();
        $product = $this->attachCategories($product);
        $response = $this->postJson('api/' . $this->currentApiVersion . '/product/', $product);
        $response->assertStatus(201);
    }

    // @test
    public function testOneRowInProductsTableWhenCreatingProduct()
    {
        $this->withoutExceptionHandling();
        $product = Product::factory()->raw();
        $product = $this->attachCategories($product);
        $this->postJson('api/' . $this->currentApiVersion . '/product/', $product);
        $this->assertDatabaseCount('products', 1);
    }

    // @test
    public function testResponse200WhenVisitShowProductUsingSlug()
    {
        $this->withoutExceptionHandling();
        $product = Product::factory()->raw();
        $product = $this->attachCategories($product);
        $this->postJson('api/' . $this->currentApiVersion . '/product', $product);
        $this->get('api/' . $this->currentApiVersion . Product::firstOrFail()->path())->assertStatus(200);
    }

    // @test
    public function testResponseContainsNameWhenVisitShowProductUsingSlug()
    {
        $this->withoutExceptionHandling();
        $product = Product::factory()->raw();
        $product = $this->attachCategories($product);
        $this->postJson('api/' . $this->currentApiVersion . '/product', $product);
        $response = $this->get('api/' . $this->currentApiVersion . Product::firstOrFail()->path())->assertStatus(200);
        $this->assertEquals($product['name'], $response['data']['product']['name']);
    }

    // @test
    public function testCanShowRecommendedProductsBasedOnProductSelection()
    {
        $this->withoutExceptionHandling();
        Category::factory(3)->create();
        Product::factory(38)->create();
        DB::table('category_product')->insert(['product_slug' => Product::find(2)->slug, 'category_slug' => Category::find(1)->slug]);
        DB::table('category_product')->insert(['product_slug' => Product::find(3)->slug, 'category_slug' => Category::find(1)->slug]);
        DB::table('category_product')->insert(['product_slug' => Product::find(4)->slug, 'category_slug' => Category::find(1)->slug]);
        $product = Product::factory()->raw(['name' => 'identified']);
        $product['categories'] = [Category::find(1)->slug, Category::find(2)->slug, Category::find(3)->slug];
        $this->postJson('api/' . $this->currentApiVersion . '/product', $product);
        $product = Product::find(39);
        $jsonResponse = $this->getJson('api/' . $this->currentApiVersion . $product->path());
        $jsonResponse->assertStatus(200);
        $this->assertCount(3, $jsonResponse['data']['recommended_products']);
    }

    // @test
    public function testUpdateProductReturns200()
    {
        $this->withoutExceptionHandling();
        $product = Product::factory()->create();
        //add edits to product
        $product->name = 'test name';
        $UpdatedProduct = $this->attachCategories($product->toArray());
        $response = $this->putJson('api/' . $this->currentApiVersion . $product->path(), $UpdatedProduct);
        $response->assertStatus(200);
    }

    // @test
    public function testProductIsUpdatedInDBAfterUpdatingRequest()
    {
        $this->withoutExceptionHandling();
        $product = Product::factory()->create();
        //add edits to product
        $product->name = 'test name';
        $UpdatedProduct = $this->attachCategories($product->toArray());
        $this->putJson('api/' . $this->currentApiVersion . $product->path(), $UpdatedProduct);
        unset($UpdatedProduct['categories']);
        $this->assertDatabaseHas('products', ['name' => $UpdatedProduct['name']]);
    }

    // @test
    public function testOnlyProductOwnerCanUpdateProduct()
    {
        $product = Product::factory()->create();
        $this->attachCategories($product->toArray());
        $updatedProduct = $this->attachCategories(Product::factory()->raw());
        $this->actingAs(Vendor::factory()->create(['email_verified_at' => Carbon::now()]));
        $response = $this->putJson('api/' . $this->currentApiVersion . $product->path(), $updatedProduct);
        $response->assertForbidden();
    }

    // @test
    public function testOnlyProductOwnerCanSoftDeleteProduct()
    {
        $product = Product::factory()->create();
        $this->attachCategories($product->toArray());
        $this->actingAs(Vendor::factory()->create(['email_verified_at' => Carbon::now()]));
        $response = $this->deleteJson('api/' . $this->currentApiVersion . $product->path());
        $response->assertForbidden();
    }

    // @test
    public function testOnlyProductOwnerCanRestoreTrashedProduct()
    {
        $product = Product::factory()->create();
        $this->attachCategories($product->toArray());
        $this->deleteJson('api/' . $this->currentApiVersion . $product->path());
        $this->actingAs(Vendor::factory()->create(['email_verified_at' => Carbon::now()]));
        $response = $this->postJson('api/' . $this->currentApiVersion . $product->path() . '/restore');
        $response->assertForbidden();
    }

    // @test
    public function testCanUpdateProductSlugWhichHasARelationToCategory()
    {
        $product = Product::factory()->raw();
        $product = $this->attachCategories($product);
        $product = $this->attachCategories($product);
        $oldProduct = $this->postJson('api/' . $this->currentApiVersion . '/product', $product)->assertSuccessful();
        $product['name'] = 'new name';
        $response = $this->putJson('api/' . $this->currentApiVersion . '/product/' . $oldProduct['data']['slug'], $product);
        $response->assertSuccessful();
        $this->assertEquals($product['name'], $response['data']['name']);
    }

    // @test
    public function testCanUpdateCategorySlugWhichHasARelationToProducts()
    {
        $category = Category::factory()->create();
        $product1 = Product::factory()->raw();
        $product2 = Product::factory()->raw();
        $product1['categories'] = $product2['categories'] = [$category->slug];
        $this->postJson('api/' . $this->currentApiVersion . '/product', $product1)->assertSuccessful();
        $this->postJson('api/' . $this->currentApiVersion . '/product', $product2)->assertSuccessful();
        $category->name = 'new name';
        $response = $this->actingAs(Admin::factory()->create(), 'admin')->putJson('api/' . $this->currentApiVersion . $category->path(), $category->toArray());
        $response->assertSuccessful();
        $this->assertEquals($category->name, $response['data']['name']);
        $products = Category::where('slug', $response['data']['slug'])->first()->products;
        $this->assertEquals($product1['slug'], $products[0]['slug']);
    }

    // @test
    public function testCanSoftDeleteProduct()
    {
        $this->withoutExceptionHandling();
        $product = Product::factory()->create();
        $this->assertDatabaseCount('products', 1);
        $this->assertNull(Product::first()->deleted_at);
        $this->deleteJson('api/' . $this->currentApiVersion . $product->path())->assertStatus(200);
        $this->assertNotNull(Product::withTrashed()->first()->deleted_at);
        $this->assertDatabaseCount('products', 1);
    }

    // @test
    public function testCanRestoreProduct()
    {
        $this->withoutExceptionHandling();
        $productModel = Product::create($product = Product::factory()->raw());
        $this->json('DELETE',  '/api/' . $this->currentApiVersion . $productModel->path(), $product)->assertStatus(200);
        $this->assertNotNull(Product::withTrashed()->first()->deleted_at);
        $response = $this->json('POST', '/api/' . $this->currentApiVersion .  $productModel->path() . '/restore', $product)->assertStatus(200);
        $this->assertNotNull($response['data']['name']);
        $this->assertNull(Product::withTrashed()->first()->deleted_at);
    }

    // @test
    public function testReturnTrashedProducts()
    {
        $this->withExceptionHandling();
        Product::factory(100)->create();
        $this->deleteJson('api/' . $this->currentApiVersion . Product::first()->path());
        $this->deleteJson('api/' . $this->currentApiVersion . Product::first()->path());
        $response = $this->getJson('api/' . $this->currentApiVersion . '/product/trashed');
        $response->assertStatus(200);
        $this->assertCount(2, $response['data']['data']);
    }

    // @test
    public function testCanPaginateAllVendorProducts()
    {
        $this->withoutExceptionHandling();
        Category::factory(3)->create();
        Product::factory(38)->create([]);
        DB::table('category_product')->insert(['product_slug' => Product::find(2)->slug, 'category_slug' => Category::find(1)->slug]);
        DB::table('category_product')->insert(['product_slug' => Product::find(3)->slug, 'category_slug' => Category::find(1)->slug]);
        DB::table('category_product')->insert(['product_slug' => Product::find(4)->slug, 'category_slug' => Category::find(1)->slug]);
        $product = Product::factory()->raw(['name' => 'identified']);
        $product['categories'] = [Category::find(1)->slug, Category::find(2)->slug, Category::find(3)->slug];
        $this->postJson('api/' . $this->currentApiVersion . '/product', $product);
        $product = Product::find(39);
        $jsonResponse = $this->getJson('api/' . $this->currentApiVersion . $product->path());
        $jsonResponse->assertStatus(200);
        $this->assertCount(3, $jsonResponse['data']['recommended_products']);
    }

    // @test
    public function testCanGetVendorOfAProduct()
    {
        Category::factory(3)->create();
        Product::factory(38)->create([]);
        DB::table('category_product')->insert(['product_slug' => Product::find(2)->slug, 'category_slug' => Category::find(1)->slug]);
        DB::table('category_product')->insert(['product_slug' => Product::find(3)->slug, 'category_slug' => Category::find(1)->slug]);
        DB::table('category_product')->insert(['product_slug' => Product::find(4)->slug, 'category_slug' => Category::find(1)->slug]);
        $product = Product::factory()->raw(['name' => 'identified']);
        $product['categories'] = [Category::find(1)->slug, Category::find(2)->slug, Category::find(3)->slug];
        $this->postJson('api/' . $this->currentApiVersion . '/product', $product);
        $product = Product::find(39);
        $jsonResponse = $this->getJson('api/' . $this->currentApiVersion . $product->path() . '/vendor');
        $jsonResponse->assertStatus(200);
    }
}
