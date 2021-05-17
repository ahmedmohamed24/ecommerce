<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class SubCategoryTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->actingAs(Admin::factory()->create(), 'admin');
    }

    // @test
    public function testCategoryMayHaveSubCategory()
    {
        $this->withoutExceptionHandling();
        Category::factory(2)->create();
        $parentCat = Category::find(1);
        $subCat = Category::find(2);
        $this->post('api/' . $this->currentApiVersion . $parentCat->path() . '/attach/sub', $subCat->toArray())->assertSuccessful();
        $this->assertDatabaseCount('sub_categories', 1);
        $response = $this->getJson('api/' . $this->currentApiVersion . $parentCat->path() . '/sub-categories');
        $response->assertSuccessful();
        $this->assertEquals($subCat->name, $response['data'][0]['name']);
    }

    // @test
    public function testCategoryMayHaveManySubCategories()
    {
        $this->withoutExceptionHandling();
        Category::factory(4)->create();
        $parentCat = Category::find(1);
        $subCat1 = Category::find(2);
        $subCat2 = Category::find(3);
        $subCat3 = Category::find(4);
        $this->post('api/' . $this->currentApiVersion . $parentCat->path() . '/attach/sub', $subCat1->toArray())->assertSuccessful();
        $this->post('api/' . $this->currentApiVersion . $parentCat->path() . '/attach/sub', $subCat2->toArray())->assertSuccessful();
        $this->post('api/' . $this->currentApiVersion . $parentCat->path() . '/attach/sub', $subCat3->toArray())->assertSuccessful();
        $this->assertDatabaseCount('sub_categories', 3);
        $this->assertEquals(3, $parentCat->subCategories()->count());
    }

    // @test
    public function testCannotHardDeleteCategoryWithAttachedSubCategories()
    {
        $this->withoutExceptionHandling();
        Category::factory(4)->create();
        $parentCat = Category::find(1);
        $subCat1 = Category::find(2);
        $subCat2 = Category::find(3);
        $subCat3 = Category::find(4);
        $this->post('api/' . $this->currentApiVersion . $parentCat->path() . '/attach/sub', $subCat1->toArray());
        $this->post('api/' . $this->currentApiVersion . $parentCat->path() . '/attach/sub', $subCat2->toArray());
        $this->post('api/' . $this->currentApiVersion . $parentCat->path() . '/attach/sub', $subCat3->toArray());
        $response = $this->deleteJson('api/' . $this->currentApiVersion . "/category/{$parentCat->slug}/delete");
        $response->assertStatus(400);
    }

    /**
     * Description: creating 3 categories and make one of them parent to the others
     * then try to get the name of the parent through the child.
     *
     * @test*/
    public function testCanFetchParentOfCategory()
    {
        $this->withoutExceptionHandling();
        Category::factory(3)->create();
        $parentCat = Category::find(1);
        $subCat1 = Category::find(2);
        $subCat2 = Category::find(3);
        $this->post('api/' . $this->currentApiVersion . $parentCat->path() . '/attach/sub', $subCat1->toArray())->assertSuccessful();
        $this->post('api/' . $this->currentApiVersion . $parentCat->path() . '/attach/sub', $subCat2->toArray())->assertSuccessful();
        $this->assertDatabaseCount('sub_categories', 2);
        $this->assertEquals($parentCat->name, $subCat1->parentCategory()->first()->name);
        $this->assertEquals($parentCat->name, $subCat2->parentCategory()->first()->name);
    }

    // @test
    public function testCategoryMayHaveNoParent()
    {
        $this->withoutExceptionHandling();
        Category::factory(2)->create();
        $this->assertCount(0, Category::find(1)->parentCategory());
    }

    // @test
    public function testCategoryMayHaveNoSubCategories()
    {
        $this->withoutExceptionHandling();
        Category::factory(2)->create();
        $this->assertCount(0, Category::find(1)->subCategories());
    }
}
