<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
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
class ProductCategoryTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->actingAs(Vendor::factory()->create(['email_verified_at' => Carbon::now()]));
    }

    // @test
    public function testAProductCouldBelongToCategories()
    {
        $this->withoutExceptionHandling();
        Category::factory(5)->create();
        $product = Product::factory()->make();
        $data = $product->toArray();
        $data['price'] = 839.3;
        $data['categories'] = [Category::find(1)->slug, Category::find(2)->slug, Category::find(4)->slug, Category::find(5)->slug];
        $response = $this->postJson('/product', $data);
        $response->assertSuccessful();
        $this->assertDatabaseCount('category_product', 4);
        $product = Product::first();
        $this->assertEquals(4, $product->categories->count());
    }

    // @test
    public function testAProductShouldHaveACategory()
    {
        $this->withoutExceptionHandling();
        $product = Product::factory()->raw(['categories' => []]);
        $response = $this->postJson('/product', $product);
        $response->assertJsonValidationErrors('categories');
    }

    // @test
    public function testCanPaginateCategoryProducts()
    {
        $this->withoutExceptionHandling();
        Category::factory(1)->create();
        $products = Product::factory(3)->raw();
        $products[0]['categories'] = [Category::find(1)->slug];
        $products[1]['categories'] = [Category::find(1)->slug];
        $products[2]['categories'] = [Category::find(1)->slug];
        $this->postJson('/product', $products[0]);
        $this->postJson('/product', $products[1]);
        $this->postJson('/product', $products[2]);
        $category = Category::first();
        $response = $this->getJson($category->path().'/products', ['slug' => $category->slug]);
        $response->assertSuccessful()->assertJsonFragment(['current_page' => 1]);
        $this->assertCount(3, $response['data']['data']);
    }

    // @test
    public function testCategoryHardDeletingDeletesAllRelatedProducts()
    {
        $this->withoutExceptionHandling();
        $category = Category::factory()->create();
        $product = Product::factory()->raw();
        $product['categories'] = [$category->slug];
        $this->postJson('/product', $product)->assertSuccessful();
        $this->assertDatabaseCount('category_product', 1);
        $response = $this->deleteJson($category->path().'/delete');
        $response->assertSuccessful();
        $dataResponse = $this->getJson('product/');
        $this->assertCount(0, $dataResponse['data']['data']);
        $this->assertDatabaseCount('category_product', 0);
    }

    public function testCategorySoftDeletingSoftDeletesAllRelatedProducts()
    {
        $this->withoutExceptionHandling();
        $category = Category::factory()->create();
        $product = Product::factory()->raw();
        $product['categories'] = [$category->slug];
        $this->postJson('/product', $product)->assertSuccessful();
        $this->assertDatabaseCount('category_product', 1);
        $response = $this->deleteJson($category->path());
        $response->assertSuccessful();
        $dataResponse = $this->getJson('product/');
        $this->assertCount(0, $dataResponse['data']['data']);
        $this->assertDatabaseCount('category_product', 1);
    }

    public function testRestoringCategoryRestoresItsProducts()
    {
        $this->withoutExceptionHandling();
        $category = Category::factory()->create();
        $product = Product::factory()->raw();
        $product['categories'] = [$category->slug];
        $this->postJson('/product', $product)->assertSuccessful();
        $this->assertDatabaseCount('category_product', 1);
        $this->deleteJson($category->path());
        $dataResponse1 = $this->getJson('product/');
        $this->assertCount(0, $dataResponse1['data']['data']);
        $this->postJson($category->path().'/restore')->assertSuccessful();
        $dataResponse = $this->getJson('product/');
        $this->assertCount(1, $dataResponse['data']['data']);
    }

    // @test
    public function testCanCategoriesReturnedWithProduct()
    {
        $this->withoutExceptionHandling();
        Category::factory(3)->create();
        $product = Product::factory()->raw();
        $product['categories'] = [Category::find(1)->slug, Category::find(2)->slug, Category::find(3)->slug];
        $this->postJson('/product', $product);
        $product = Product::first();
        $jsonResponse = $this->get($product->path())->assertStatus(200);
        $this->assertCount(3, $jsonResponse['data']['product']['categories']);
    }
}
