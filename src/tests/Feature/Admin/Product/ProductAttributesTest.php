<?php

namespace Tests\Feature\Admin\Product;

use App\Models\Admin;
use App\Models\Attribute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ProductAttributesTest extends TestCase
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
    public function testStatusIs201WhenAdminCreatingAttributeForProducts()
    {
        $this->withoutExceptionHandling();
        $attribute = Attribute::factory()->raw();
        $response = $this->postJson(\route('attribute.store'), $attribute);
        $response->assertStatus(201);
    }

    // @test
    public function testAttributeNameIsSavedInDBWhenAdminCreatingAttributeForProducts()
    {
        $this->withoutExceptionHandling();
        $attribute = Attribute::factory()->raw();
        $this->postJson(\route('attribute.store'), $attribute);
        $this->assertDatabaseHas('attributes', ['name' => $attribute['name']]);
    }

    // @test
    public function testAttributeSlugIsSavedInDBWhenAdminCreatingAttributeForProducts()
    {
        $this->withoutExceptionHandling();
        $attribute = Attribute::factory()->raw();
        $this->postJson(\route('attribute.store'), $attribute);
        $this->assertDatabaseHas('attributes', ['slug' => $attribute['slug']]);
    }

    // @test
    public function testAttributeSlugIsUnique()
    {
        $this->withoutExceptionHandling();
        $attribute = Attribute::factory()->raw();
        $this->postJson(\route('attribute.store'), $attribute);
        $response = $this->postJson(\route('attribute.store'), $attribute);
        $response->assertStatus(406);
    }

    // @test
    public function testOnlyAdminCanAddAttribute()
    {
        $attribute = Attribute::factory()->raw();
        \auth('admin')->logout();
        $this->actingAs(User::factory()->create())->postJson(\route('attribute.store'), $attribute);
        $response = $this->postJson(\route('attribute.store'), $attribute);
        $response->assertStatus(403);
    }

    // @test
    public function testSuccessMsgIsReturnedFromMethod()
    {
        $this->withoutExceptionHandling();
        $attribute = Attribute::factory()->raw();
        $response = $this->postJson(\route('attribute.store'), $attribute);
        $this->assertEquals('success', $response['message']);
    }
}
