<?php

namespace Tests\Feature;

use App\Models\Category;
use Tests\TestCase;
use App\Models\Product;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ProductTest extends TestCase
{
    use WithFaker,RefreshDatabase;
    /**@test */
    public function test_can_create_product_with_factory()
    {
        $this->withoutExceptionHandling();
        $product=Product::factory()->create();
        $this->assertDatabaseHas('products', $product->only('name', 'slug', 'description'));
    }
    /**@test*/
    public function test_user_can_create_product()
    {
        $this->withoutExceptionHandling();
        $product=Product::factory()->raw(['name'=>'this is a test for slug']);
        $category=Category::factory()->create();
        $product['categories']=[$category->slug];
        $response=$this->postJson('/product/', $product);
        $response->assertStatus(200);
        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('category_product', 1);
    }
    public function test_product_name_should_be_unique()
    {
        $this->withoutExceptionHandling();
        Product::factory()->create(['name'=>'this is a test']);
        $product=Product::factory()->raw(['name'=>'this is a test']);
        $this->postJson('/product/', $product)->assertStatus(Response::HTTP_NOT_ACCEPTABLE);
        $this->assertDatabaseCount('products', 1);
    }
    /**@test*/
    public function test_can_show_product_using_slug()
    {
        $this->withoutExceptionHandling();
        Category::factory(3)->create();
        $product=Product::factory()->raw();
        $product['categories']=[Category::find(1)->slug,Category::find(2)->slug,Category::find(3)->slug];
        $this->postJson('/product', $product);
        $product=Product::first();
        $jsonResponse=$this->get($product->path())->assertStatus(200);
        $this->assertEquals($product->name, $jsonResponse['data']['product']['name']);
    }
    /**@test*/
    public function test_can_categories_returned_with_product()
    {
        $this->withoutExceptionHandling();
        Category::factory(3)->create();
        $product=Product::factory()->raw();
        $product['categories']=[Category::find(1)->slug,Category::find(2)->slug,Category::find(3)->slug];
        $this->postJson('/product', $product);
        $product=Product::first();
        $jsonResponse=$this->get($product->path())->assertStatus(200);
        $this->assertCount(3, $jsonResponse['data']['product']['categories']);
    }
    /**@test*/
    public function test_can_show_recommended_products_based_on_product_selection()
    {
        $this->withoutExceptionHandling();
        Category::factory(3)->create();
        Product::factory(38)->create();
        DB::table('category_product')->insert(['product_slug'=>Product::find(2)->slug,'category_slug'=>Category::find(1)->slug]);
        DB::table('category_product')->insert(['product_slug'=>Product::find(3)->slug,'category_slug'=>Category::find(1)->slug]);
        DB::table('category_product')->insert(['product_slug'=>Product::find(4)->slug,'category_slug'=>Category::find(1)->slug]);
        $product=Product::factory()->raw(['name'=>'identified']);
        $product['categories']=[Category::find(1)->slug,Category::find(2)->slug,Category::find(3)->slug];
        $this->postJson('/product', $product);
        $product=Product::find(39);
        $jsonResponse=$this->getJson($product->path());
        $jsonResponse->assertStatus(200);
        $this->assertCount(3, $jsonResponse['data']['recommended_products']);
    }
    /**@test*/
    public function test_can_show_only_exist_products_or_return_error()
    {
        $this->withoutExceptionHandling();
        $product=Product::factory()->make();
        $this->get($product->path())->assertStatus(404);
    }
    /**@test*/
    public function test_can_update_product()
    {
        $this->withoutExceptionHandling();
        $category=Category::factory()->create();
        $catSlug=$category->slug;
        $product=Product::factory()->create();
        $product->name='new name';
        $requestData=$product->toArray();
        $requestData['categories']=[$catSlug];
        $this->putJson($product->path(), $requestData)->assertSuccessful();
        $this->assertEquals(Product::first()->name, 'new name');
        $this->assertEquals(Product::first()->slug, 'new-name');
    }
    /**@test*/
    public function test_can_update_product_slug_which_has_a_relation_to_category()
    {
        $category=Category::factory()->create();
        $category1=Category::factory()->create();
        $product=Product::factory()->raw();
        $product['categories']=[];
        \array_push($product['categories'], $category->slug, $category1->slug);
        $oldProduct=$this->postJson('product', $product)->assertSuccessful();
        $product['name']='new name';
        $response=$this->putJson('product/'.$oldProduct['data']['slug'], $product);
        $response->assertSuccessful();
        $this->assertEquals($product['name'], $response['data']['name']);
        $categories=Product::where('slug', $response['data']['slug'])->first()->categories;
        $this->assertEquals($category->slug, $categories[0]['slug']);
    }
    /**@test*/
    public function test_can_update_category_slug_which_has_a_relation_to_products()
    {
        $category=Category::factory()->create();
        $product1=Product::factory()->raw();
        $product2=Product::factory()->raw();
        $product1['categories']=$product2['categories']=[$category->slug];
        $this->postJson('product', $product1)->assertSuccessful();
        $this->postJson('product', $product2)->assertSuccessful();
        $category->name='new name';
        $response=$this->putJson($category->path(), $category->toArray());
        $response->assertSuccessful();
        $this->assertEquals($category->name, $response['data']['name']);
        $products=Category::where('slug', $response['data']['slug'])->first()->products;
        $this->assertEquals($product1['slug'], $products[0]['slug']);
    }
    /**@test*/
    public function test_can_soft_delte_product()
    {
        $this->withoutExceptionHandling();
        $product=Product::factory()->create();
        $this->assertDatabaseCount('products', 1);
        $this->assertNull(Product::first()->deleted_at);
        $this->deleteJson($product->path())->assertStatus(200);
        $this->assertNotNull(Product::withTrashed()->first()->deleted_at);
        $this->assertDatabaseCount('products', 1);
    }
    /**@test */
    public function test_can_restore_product()
    {
        $this->withoutExceptionHandling();
        $productModel=Product::create($product=Product::factory()->raw());
        $this->json('DELETE', $productModel->path(), $product)->assertStatus(200);
        $this->assertNotNull(Product::withTrashed()->first()->deleted_at);
        $this->json('POST', $productModel->path().'/restore', $product)->assertStatus(200);
        $this->assertNull(Product::withTrashed()->first()->deleted_at);
    }
    /**@test*/
    public function test_can_paginate_products()
    {
        $this->withoutExceptionHandling();
        Product::factory(100)->create();
        $this->getJson('/product?page=2')->assertStatus(200)->assertJsonFragment(['current_page'=>2]);
    }
    /**@test */
    public function test_return_prducts_in_random_order()
    {
        $this->withExceptionHandling();
        Product::factory(100)->create();
        $this->getJson('/product/random')->assertStatus(200)->assertJsonFragment(['message'=>'success']);
    }
    /**@test */
    public function test_return_trashed_prducts()
    {
        $this->withExceptionHandling();
        Product::factory(100)->create();
        $this->deleteJson(Product::first()->path());
        $this->deleteJson(Product::first()->path());
        $reponse=$this->getJson('/product/trashed')->assertStatus(200);
        $this->assertCount(2, $reponse['data']['data']);
    }
    public function test_price_is_in_money_format()
    {
        $product=Product::factory()->create(['price'=>522.232]);
        $this->assertEquals($product->formattedPrice(), '$522.23');
    }
}
