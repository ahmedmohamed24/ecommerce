<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Category;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryTest extends TestCase
{
    use RefreshDatabase,WithFaker;
    /**@test*/
    public function test_category_has_path_function()
    {
        $category=Category::factory()->create();
        $this->assertNotNull($category->path());
        $this->assertIsString($category->path());
    }
}
