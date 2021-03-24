<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductCategoryTest extends TestCase
{
    use WithFaker,RefreshDatabase;
    /**@test*/
    public function test_a_product_could_belong_to_categories()
    {
        $this->withoutExceptionHandling();
        Category::factory(5)->create();
        $product=Product::factory()->make();
        $data=$product->toArray();
        $data['price']=839.3;
        $data['categories'] =[Category::find(1)->slug, Category::find(2)->slug,Category::find(4)->slug,Category::find(5)->slug];
        $response=$this->postJson('/product', $data);
        $response->assertSuccessful();
        $this->assertDatabaseCount('category_product', 4);
        $product=Product::first();
        $this->assertEquals(4, $product->categories->count());
    }
    /**@test*/
    public function test_a_product_should_have_a_category()
    {
        $this->withoutExceptionHandling();
        $product=Product::factory()->raw(['categories'=>[]]);
        $response=$this->postJson('/product', $product);
        $response->assertJsonValidationErrors('categories');
    }
    /**@test*/
    public function test_can_paginate_category_products()
    {
        $this->withoutExceptionHandling();
        Category::factory(1)->create();
        $products=Product::factory(3)->raw();
        $products[0]['categories']=[Category::find(1)->slug];
        $products[1]['categories']=[Category::find(1)->slug];
        $products[2]['categories']=[Category::find(1)->slug];
        $this->postJson('/product', $products[0]);
        $this->postJson('/product', $products[1]);
        $this->postJson('/product', $products[2]);
        $category=Category::first();
        $response=$this->postJson($category->path().'/products', ['slug'=>$category->slug]);
        $response->assertSuccessful()->assertJsonFragment(['current_page'=> 1]);
        $this->assertCount(3, $response['data']['data']);
    }
}
