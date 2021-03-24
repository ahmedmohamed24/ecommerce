<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryTest extends TestCase
{
    use WithFaker,RefreshDatabase;
    /**@test*/
    public function test_can_create_category()
    {
        $this->withoutExceptionHandling();
        $category=Category::factory()->raw();
        $response=$this->post('/category', $category)->assertStatus(200);
        $this->assertDatabaseCount('categories', 1);
        $this->assertEquals('success', $response['message']);
        $this->assertEquals($category['name'], $response['data']['name']);
    }
    /**@test*/
    public function test_category_name_is_required()
    {
        $this->withoutExceptionHandling();
        $category=Category::factory()->raw(['name'=>'']);
        $response=$this->post('/category', $category)->assertStatus(Response::HTTP_NOT_ACCEPTABLE);
        $this->assertNotNull($response['errors']['name']);
    }
    /**@test*/
    public function test_category_name_is_unique()
    {
        $this->withoutExceptionHandling();
        Category::create($category=Category::factory()->raw());
        $response=$this->post('/category', $category)->assertStatus(Response::HTTP_NOT_ACCEPTABLE);
        $this->assertNotNull($response['data']['errors']['name']);
    }
    /**@test*/
    public function test_retrieve_category()
    {
        $this->withoutExceptionHandling();
        $category=Category::factory()->create();
        $response=$this->get($category->path())->assertStatus(200);
        $this->assertEquals($response['data']['slug'], $category->slug);
    }
    /**@test*/
    public function test_exception_if_not_found()
    {
        $this->withoutExceptionHandling();
        $category=Category::factory()->make();
        $response=$this->get($category->path())->assertStatus(404);
        $this->assertEquals('Not Found', $response['message']);
    }
    /**@test*/
    public function test_update_category()
    {
        $this->withoutExceptionHandling();
        $category=Category::create($categoryData=Category::factory()->raw());
        $categoryData['name']='new category/name';
        $response=$this->put($category->path(), $categoryData)->assertStatus(200);
        $this->assertEquals(Str::slug('new category/name'), Category::first()->slug);
        $this->assertTrue($response['data']);
    }
    /**@test*/
    public function test_delete_category()
    {
        $this->withoutExceptionHandling();
        $category=Category::factory()->create();
        $this->delete($category->path())->assertStatus(200);
        $this->assertDatabaseCount('categories', 1);//soft_delete
        $this->assertEquals(0, Category::count());
    }
    /**@test*/
    public function test_permanent_delete_category()
    {
        $this->withoutExceptionHandling();
        $category=Category::factory()->create();
        $this->delete($category->path().'/delete')->assertStatus(200);
        $this->assertDatabaseCount('categories', 0);//hard_delete
    }
    /**@test*/
    public function test_restore_soft_deleted_category()
    {
        $this->withoutExceptionHandling();
        $category=Category::factory()->create();
        $this->delete($category->path())->assertStatus(200);
        $this->get($category->path().'/restore')->assertStatus(200);
        $this->assertEquals(1, Category::count());
    }
    /**@test*/
    public function test_paginate_categories()
    {
        $this->withoutExceptionHandling();
        Category::factory(28)->create();
        $this->get('/category?page=2')->assertStatus(200)->assertJsonFragment(['current_page'=>2]);
    }
    /**@test*/
    public function test_paginate_archived()//soft deleted items
    {
        $this->withoutExceptionHandling();
        Category::factory(28)->create();
        Category::inRandomOrder()->take(15)->get()->map(function ($category) {
            $category->delete();
        });
        $this->get('/category/trashed?page=1')->assertStatus(200)->assertJsonFragment(['current_page'=>1]);
    }
    /**@test*/
    public function test_can_create_thumnail_to_category()
    {
        $this->withoutExceptionHandling();
        $category=Category::factory()->raw();
        $category['thumbnail']=UploadedFile::fake()->image('random.jpg');
        $this->post('/category', $category, )->assertSuccessful();
        $this->fileExists(\public_path(Category::first()->thumbnail));
    }
}
