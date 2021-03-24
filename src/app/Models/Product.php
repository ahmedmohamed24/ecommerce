<?php

namespace App\Models;

use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory,SoftDeletes;

    const PRODUCTS_FOR_RECOMMENDATION=10;
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
    /**
     *  return a uniform path so we can use slug or id or anly other identifier
     *
     * @return string
     */
    public function path()
    {
        return '/product/'.$this->slug;
    }
    /**
     *  getters of laravel to format price in money format
     *
     * @param [type] $price
     * @return void
     */
    public function getPriceAttribute($value)
    {
        return '$'.number_format(($value), 2);
    }
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_product', 'product_slug', 'category_slug', 'slug', 'slug');
    }
    public function recommendations()
    {
        $recommedationCondition=\substr($this->slug, 0, \strpos($this->slug, '-') > 0 ? \strpos($this->slug, '-'): \strlen($this->slug));
        $recommendedProduct=self::where('slug', '!=', $this->slug)->where('slug', 'LIKE', "%$recommedationCondition%")->inRandomOrder()->take(self::PRODUCTS_FOR_RECOMMENDATION)->get();
        //if the recommended products (fetched by slug) is less than  PRODUCTS_FOR_RECOMMENDATION then try another way
        if ($recommendedProduct->count() < self::PRODUCTS_FOR_RECOMMENDATION) {
            $category= $this->categories->first();
            $restOfRequiredRecommended=$category->products->take(self::PRODUCTS_FOR_RECOMMENDATION-$recommendedProduct->count());
            $currentSlug = $this->slug;
            $recommendedProduct=$recommendedProduct->merge($restOfRequiredRecommended)->reject(function ($product) use ($currentSlug) {
                return $product->slug === $currentSlug;
            });
            // dd($recommendedProduct);
        }
        return $recommendedProduct;
    }
}
