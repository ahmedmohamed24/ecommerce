<?php

namespace Tests\Feature\Admin\Category;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class CategoryTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->actingAs(Admin::factory()->create(), 'admin');
    }

    // @test
    public function testCanCreateCategory()
    {
        $this->withoutExceptionHandling();
        $category = Category::factory()->raw();
        $response = $this->post('api/'.$this->currentApiVersion.'/category', $category);
        $response->assertStatus(200);
        $this->assertDatabaseCount('categories', 1);
        $this->assertEquals('success', $response['message']);
        $this->assertEquals($category['name'], $response['data']['name']);
    }

    // @test
    public function testCategoryNameIsRequired()
    {
        $this->withoutExceptionHandling();
        $category = Category::factory()->raw(['name' => '']);
        $response = $this->post('api/'.$this->currentApiVersion.'/category', $category)->assertStatus(Response::HTTP_NOT_ACCEPTABLE);
        $this->assertNotNull($response['errors']['name']);
    }

    // @test
    public function testCategoryNameIsUnique()
    {
        $this->withoutExceptionHandling();
        Category::create($category = Category::factory()->raw());
        $response = $this->post('api/'.$this->currentApiVersion.'/category', $category)->assertStatus(Response::HTTP_NOT_ACCEPTABLE);
        $this->assertNotNull($response['data']['errors']['name']);
    }

    // @test
    public function testRetrieveCategory()
    {
        $this->withoutExceptionHandling();
        $category = Category::factory()->create();
        $response = $this->get('api/'.$this->currentApiVersion.$category->path())->assertStatus(200);
        $this->assertEquals($response['data']['slug'], $category->slug);
    }

    // @test
    public function testExceptionIfCategoryNotFound()
    {
        $this->withoutExceptionHandling();
        $category = Category::factory()->make();
        $response = $this->get('api/'.$this->currentApiVersion.$category->path())->assertStatus(404);
        $this->assertEquals('Not Found', $response['message']);
    }

    // @test
    public function testUpdateCategory()
    {
        $this->withoutExceptionHandling();
        $category = Category::create($categoryData = Category::factory()->raw());
        $categoryData['name'] = 'new category/name';
        $response = $this->put('api/'.$this->currentApiVersion.$category->path(), $categoryData)->assertStatus(200);
        $this->assertEquals(Str::slug('new category/name'), Category::first()->slug);
        $this->assertEquals($categoryData['name'], $response['data']['name']);
    }

    // @test
    public function testDeleteCategory()
    {
        $this->withoutExceptionHandling();
        $category = Category::factory()->create();
        $this->delete('api/'.$this->currentApiVersion.$category->path())->assertStatus(200);
        $this->assertDatabaseCount('categories', 1); //soft_delete
        $this->assertEquals(0, Category::count());
    }

    // @test
    public function testPermanentDeleteCategory()
    {
        $this->withoutExceptionHandling();
        $category = Category::factory()->create();
        $this->delete('api/'.$this->currentApiVersion.$category->path().'/delete')->assertStatus(200);
        $this->assertDatabaseCount('categories', 0); //hard_delete
    }

    // @test
    public function testRestoreSoftDeletedCategory()
    {
        $this->withoutExceptionHandling();
        $category = Category::factory()->create();
        $this->delete('api/'.$this->currentApiVersion.$category->path())->assertStatus(200);
        $this->post('api/'.$this->currentApiVersion.$category->path().'/restore')->assertStatus(200);
        $this->assertEquals(1, Category::count());
    }

    // @test
    public function testPaginateCategories()
    {
        $this->withoutExceptionHandling();
        Category::factory(28)->create();
        $this->get('/api/'.$this->currentApiVersion.'/category?page=2')->assertStatus(200)->assertJsonFragment(['current_page' => 2]);
    }

    // @test
    public function testPaginateArchived() //soft deleted items
    {
        $this->withoutExceptionHandling();
        Category::factory(28)->create();
        Category::inRandomOrder()->take(15)->get()->map(function ($category) {
            $category->delete();
        });
        $this->get('api/'.$this->currentApiVersion.'/category/trashed?page=1')->assertStatus(200)->assertJsonFragment(['current_page' => 1]);
    }

    // @test
    public function testCanCreateThumbnailToCategory()
    {
        $this->withoutExceptionHandling();
        $category = Category::factory()->raw();
        $category['thumbnail'] = UploadedFile::fake()->image('random.jpg');
        $this->post('api/'.$this->currentApiVersion.'/category', $category, )->assertSuccessful();
        $this->fileExists(\public_path(Category::first()->thumbnail));
    }

    public function testCanHardDeleteCategoryWithAttachedProducts()
    {
        $category = Category::factory()->create();
        $products = Product::factory(2)->create();
        \DB::table('category_product')->insert(['category_slug' => $category->slug, 'product_slug' => $products->get(0)->slug]);
        \DB::table('category_product')->insert(['category_slug' => $category->slug, 'product_slug' => $products->get(1)->slug]);
        $response = $this->deleteJson('api/'.$this->currentApiVersion.'/category/'.Category::first()->slug.'/delete');
        $response->assertSuccessful();
        $this->assertDatabaseCount('products', 0);
    }
}
