<?php

namespace Tests\Feature\Vendor\Product;

use App\Models\Attribute;
use App\Models\AttributeOption;
use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
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

    public function setup(): void
    {
        parent::setUp();
        Attribute::factory(3)->create();
        Category::factory(3)->create();
        AttributeOption::factory(3)->create();
        $this->actingAs(Vendor::factory()->create(['email_verified_at' => \Carbon\Carbon::now()]), 'vendor');
    }

    // @test
    public function testProductAttributesIsSavedIntoDB()
    {
        $this->withoutExceptionHandling();
        $product = $this->attachCategories(Product::factory()->raw(
            ['attribute' => [Attribute::first()->slug], 'attributesValues' => [[AttributeOption::first()->slug, AttributeOption::findOrFail(2)->slug]]]
        ));
        $this->postJson(\route('vendor.product.store'), $product);
        $this->assertDatabaseCount('product_attribute', 2);
    }

    // @test
    public function testStatus201AfterCreation()
    {
        $this->withoutExceptionHandling();
        $product = $this->attachCategories(Product::factory()->raw(
            ['attribute' => [Attribute::first()->slug], 'attributesValues' => [[AttributeOption::first()->slug, AttributeOption::findOrFail(2)->slug]]]
        ));
        $response = $this->postJson(\route('vendor.product.store'), $product);
        $response->assertStatus(201);
    }

    // @test
    public function testStatus406ReturnedIfAttributesAndValuesAreNotTheSameLength()
    {
        $this->withoutExceptionHandling();
        $product = $this->attachCategories(Product::factory()->raw(
            ['attribute' => [Attribute::first()->slug], 'attributesValues' => [[AttributeOption::first()->slug], [AttributeOption::first()->slug]]]
        ));
        $response = $this->postJson(\route('vendor.product.store'), $product);
        $response->assertStatus(406);
    }

    // @test
    public function testStatus406ReturnedIfAttributeIsStringNotArray()
    {
        $this->withoutExceptionHandling();
        $product = $this->attachCategories(Product::factory()->raw(
            ['attribute' => Attribute::first()->slug, 'attributesValues' => [[AttributeOption::first()->slug]]]
        ));
        $response = $this->postJson(\route('vendor.product.store'), $product);
        $response->assertStatus(406);
    }

    // @test
    public function testCouldGetProductWithItsOptions()
    {
        $this->withoutExceptionHandling();
        $product = $this->attachCategories(Product::factory()->raw(
            ['attribute' => [Attribute::first()->slug], 'attributesValues' => [[AttributeOption::first()->slug, AttributeOption::findOrFail(2)->slug]]]
        ));
        $this->postJson(\route('vendor.product.store'), $product)->assertStatus(201);
        $response = $this->getJson(\route('user.product.show', $product['slug']), $product);
        $this->assertCount(2, $response['data']['product']['attributes']);
    }
}
