<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SubCategoryTest extends TestCase
{
    use WithFaker,RefreshDatabase;
    /**@test*/
    public function test_category_may_have_sub_category()
    {
        $this->withoutExceptionHandling();
        Category::factory(2)->create();
        $parentCat=Category::find(1);
        $subCat=Category::find(2);
        $this->post($parentCat->path().'/attach/sub', $subCat->toArray())->assertSuccessful();
        $this->assertDatabaseCount('sub_categories', 1);
        $response=$this->getJson($parentCat->path().'/sub-categories');
        $response->assertSuccessful();
        $this->assertEquals($subCat->name, $response['data'][0]['name']);
    }
    /**@test*/
    public function test_category_may_have_many_sub_categories()
    {
        $this->withoutExceptionHandling();
        Category::factory(4)->create();
        $parentCat=Category::find(1);
        $subCat1=Category::find(2);
        $subCat2=Category::find(3);
        $subCat3=Category::find(4);
        $this->post($parentCat->path().'/attach/sub', $subCat1->toArray())->assertSuccessful();
        $this->post($parentCat->path().'/attach/sub', $subCat2->toArray())->assertSuccessful();
        $this->post($parentCat->path().'/attach/sub', $subCat3->toArray())->assertSuccessful();
        $this->assertDatabaseCount('sub_categories', 3);
        $this->assertEquals(3, $parentCat->subCategories()->count());
    }
    /**
     * Description: creating 3 categories and make one of them parent to the others
     * then try to get the name of the parent through the child
     * @test*/
    public function test_can_fetch_parent_of_category()
    {
        $this->withoutExceptionHandling();
        Category::factory(3)->create();
        $parentCat=Category::find(1);
        $subCat1=Category::find(2);
        $subCat2=Category::find(3);
        $this->post($parentCat->path().'/attach/sub', $subCat1->toArray())->assertSuccessful();
        $this->post($parentCat->path().'/attach/sub', $subCat2->toArray())->assertSuccessful();
        $this->assertDatabaseCount('sub_categories', 2);
        $this->assertEquals($parentCat->name, $subCat1->parentCategory()->first()->name);
        $this->assertEquals($parentCat->name, $subCat2->parentCategory()->first()->name);
    }
    /**@test */
    public function test_category_may_have_no_parent()
    {
        $this->withoutExceptionHandling();
        Category::factory(2)->create();
        $this->assertCount(0, Category::find(1)->parentCategory());
    }
    /**@test */
    public function test_category_may_have_no_sub_categories()
    {
        $this->withoutExceptionHandling();
        Category::factory(2)->create();
        $this->assertCount(0, Category::find(1)->subCategories());
    }
}
