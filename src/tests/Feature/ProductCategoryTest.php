<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
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
    // /**@test*/
    // public function test_a_product_should_have_a_category()
    // {
    //     $this->withoutExceptionHandling();
    // }
    // /**@test*/
    // public function test_can_paginate_category_products()
    // {
    //     $this->withoutExceptionHandling();
    //     $category=Category::factory()->create();
    // }
}
