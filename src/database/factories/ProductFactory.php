<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

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
        return [
            'name'=>$this->faker->unique()->word(),
            'slug'=>$this->faker->unique()->slug(),
            'details'=>$this->faker->sentence(20),
            'description'=>$this->faker->sentence(50),
            'price'=>$this->faker->randomFloat(6, 1, 999999.99),

        ];
    }
}
