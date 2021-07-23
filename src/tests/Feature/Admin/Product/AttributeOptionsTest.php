<?php

namespace Tests\Feature\Admin\Product;

use App\Models\Admin;
use App\Models\AttributeOption;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class AttributeOptionsTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $admin = Admin::factory()->raw();
        Admin::create($admin);
        $admin['password'] = 'password';
        $this->post('api/'.$this->currentApiVersion.'/admin/login', $admin)->assertStatus(200);
    }

    // @test
    public function testStatus201WhenAddingOptionToAttribute()
    {
        $option = AttributeOption::factory()->raw();
        $response = $this->postJson(\route('attribute.option.store'), $option);
        $response->assertStatus(201);
    }

    // @test
    public function testOptionNameIsSavedIntoDBWhenAddingOptionToAttribute()
    {
        $option = AttributeOption::factory()->raw();
        $this->postJson(\route('attribute.option.store'), $option);
        $this->assertDatabaseHas('attribute_options', ['name' => $option['name']]);
    }

    // @test
    public function testOptionSlugIsSavedIntoDBWhenAddingOptionToAttribute()
    {
        $option = AttributeOption::factory()->raw();
        $this->postJson(\route('attribute.option.store'), $option);
        $this->assertDatabaseHas('attribute_options', ['slug' => $option['slug']]);
    }

    // @test
    public function testStatus406WhenDuplicatingSlug()
    {
        $option = AttributeOption::factory()->raw();
        $this->postJson(\route('attribute.option.store'), $option);
        $response = $this->postJson(\route('attribute.option.store'), $option);
        $response->assertStatus(406);
    }

    // @test
    public function testSuccessMessageWhenCreatingNewOption()
    {
        $option = AttributeOption::factory()->raw();
        $response = $this->postJson(\route('attribute.option.store'), $option);
        $this->assertEquals('success', $response['message']);
    }
}
