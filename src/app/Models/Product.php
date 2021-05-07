<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    const PRODUCTS_FOR_RECOMMENDATION = 10;
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     *  return a uniform path so we can use slug or id or any other identifier.
     *
     * @return string
     */
    public function path()
    {
        return '/product/'.$this->slug;
    }

    /**
     *  getters to format price in money format.
     *
     * @param [type] $price
     */
    public function formattedPrice()
    {
        return '$'.number_format(($this->price), 2);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_product', 'product_slug', 'category_slug', 'slug', 'slug');
    }

    public function recommendations()
    {
        $recommendationCondition = \substr($this->slug, 0, \strpos($this->slug, '-') > 0 ? \strpos($this->slug, '-') : \strlen($this->slug));
        $recommendedProduct = self::where('slug', '!=', $this->slug)->where('slug', 'LIKE', "%{$recommendationCondition}%")->inRandomOrder()->take(self::PRODUCTS_FOR_RECOMMENDATION)->get();
        //check the recommended products (fetched by slug) is less than  PRODUCTS_FOR_RECOMMENDATION then try another way
        if ($recommendedProduct->count() < self::PRODUCTS_FOR_RECOMMENDATION) {
            $category = $this->categories->first();
            $restOfRequiredRecommended = $category->products->take(self::PRODUCTS_FOR_RECOMMENDATION - $recommendedProduct->count());
            $currentSlug = $this->slug;
            $recommendedProduct = $recommendedProduct->merge($restOfRequiredRecommended)->reject(function ($product) use ($currentSlug) {
                return $product->slug === $currentSlug;
            });
        }

        return $recommendedProduct;
    }

    public function getOwner()
    {
        return $this->belongsTo(Vendor::class, 'owner', 'id');
    }
}
