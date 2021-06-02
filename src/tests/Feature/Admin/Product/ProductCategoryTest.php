<?php

namespace Tests\Feature\Admin\Product;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
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
        $this->actingAs(Vendor::factory()->create(), 'vendor');
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
        $response = $this->postJson('api/'.$this->currentApiVersion.'/product', $data);
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
        $response = $this->postJson('api/'.$this->currentApiVersion.'/product', $product);
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
        $this->postJson('api/'.$this->currentApiVersion.'/product', $products[0]);
        $this->postJson('api/'.$this->currentApiVersion.'/product', $products[1]);
        $this->postJson('api/'.$this->currentApiVersion.'/product', $products[2]);
        $category = Category::first();
        $response = $this->getJson('api/'.$this->currentApiVersion.$category->path().'/products', ['slug' => $category->slug]);
        $response->assertSuccessful()->assertJsonFragment(['current_page' => 1]);
        $this->assertCount(3, $response['data']['data']);
    }

    public function helperCreateCategoryWithProducts()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->raw();
        $product['categories'] = [$category->slug];
        $this->postJson('api/'.$this->currentApiVersion.'/product', $product)->assertSuccessful();
        $this->assertDatabaseCount('category_product', 1);

        return $category;
    }

    // @test
    public function testCategoryHardDeletingDeletesAllRelatedProducts()
    {
        $this->withoutExceptionHandling();
        $category = $this->helperCreateCategoryWithProducts();
        $this->actingAs(Admin::factory()->create(), 'admin');
        $response = $this->deleteJson('api/'.$this->currentApiVersion.$category->path().'/delete');
        $response->assertSuccessful();
        $dataResponse = $this->getJson('api/'.$this->currentApiVersion.'/product/');
        $this->assertCount(0, $dataResponse['data']['data']);
        $this->assertDatabaseCount('category_product', 0);
    }

    public function testCategorySoftDeletingSoftDeletesAllRelatedProducts()
    {
        $this->withoutExceptionHandling();
        $category = Category::factory()->create();
        $product = Product::factory()->raw();
        $product['categories'] = [$category->slug];
        $this->postJson('api/'.$this->currentApiVersion.'/product', $product)->assertSuccessful();
        $this->assertDatabaseCount('category_product', 1);
        $this->actingAs(Admin::factory()->create(), 'admin');
        $response = $this->deleteJson('api/'.$this->currentApiVersion.$category->path());
        $response->assertSuccessful();
        $dataResponse = $this->getJson('api/'.$this->currentApiVersion.'/product/');
        $this->assertCount(0, $dataResponse['data']['data']);
        $this->assertDatabaseCount('category_product', 1);
    }

    public function testRestoringCategoryRestoresItsProducts()
    {
        $this->withoutExceptionHandling();
        $category = Category::factory()->create();
        $product = Product::factory()->raw();
        $product['categories'] = [$category->slug];
        $this->postJson('api/'.$this->currentApiVersion.'/product', $product)->assertSuccessful();
        $this->assertDatabaseCount('category_product', 1);
        $this->actingAs(Admin::factory()->create(), 'admin');
        $this->deleteJson('api/'.$this->currentApiVersion.$category->path());
        $dataResponse1 = $this->getJson('api/'.$this->currentApiVersion.'/product/');
        $this->assertCount(0, $dataResponse1['data']['data']);
        $this->postJson('api/'.$this->currentApiVersion.$category->path().'/restore')->assertSuccessful();
        $dataResponse = $this->getJson('api/'.$this->currentApiVersion.'/product/');
        $this->assertCount(1, $dataResponse['data']['data']);
    }

    // @test
    public function testCanCategoriesReturnedWithProduct()
    {
        $this->withoutExceptionHandling();
        Category::factory(3)->create();
        $product = Product::factory()->raw();
        $product['categories'] = [Category::find(1)->slug, Category::find(2)->slug, Category::find(3)->slug];
        $this->postJson('api/'.$this->currentApiVersion.'/product', $product);
        $product = Product::first();
        $jsonResponse = $this->get('api/'.$this->currentApiVersion.$product->path())->assertStatus(200);
        $this->assertCount(3, $jsonResponse['data']['product']['categories']);
    }
}
