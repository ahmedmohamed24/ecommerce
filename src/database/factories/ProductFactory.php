<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = $this->faker->unique()->word();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'details' => $this->faker->sentence(20),
            'description' => $this->faker->sentence(50),
            'owner' => auth('vendor')->check() ? auth()->id() : Vendor::factory()->create()->id,
            'price' => $this->faker->randomFloat(6, 1, 999999.99),
        ];
    }
}
