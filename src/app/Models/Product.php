<?php

namespace App\Models;

use App\Models\Attribute;
use Laravel\Scout\Searchable;
use App\Http\Traits\JsonResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use JsonResponse;
    use HasFactory;
    use SoftDeletes;
    use Searchable;

    const PRODUCTS_FOR_RECOMMENDATION = 10;
    /**
     * The attribute that aren't mass assignable.
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
        return '/product/' . $this->slug;
    }

    /**
     *  getters to format price in money format.
     *
     * @param [type] $price
     */
    public function formattedPrice()
    {
        return '$' . number_format(($this->price), 2);
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

    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'product_attribute', 'product_slug', 'attribute_slug', 'slug', 'slug')
            ->join('attribute_options AS ao', 'ao.slug', "=", 'product_attribute.attribute_value_slug')
            ->select('ao.name AS value', 'attributes.name as option');
    }


    public function getOwner()
    {
        return $this->belongsTo(Vendor::class, 'owner', 'id');
    }

    /**
     * Get the name of the index associated with the model.
     *
     * @return string
     */
    public function searchableAs()
    {
        return 'products_slug';
    }

    public function attachAttributes(array $attribute = null, array $attributesValues = null)
    {
        for ($i = 0; $i < \count($attribute); ++$i) {
            foreach ($attributesValues[$i] as $attrValue) {
                \DB::table('product_attribute')->insert([
                    'product_slug' => $this->slug,
                    'attribute_slug' => $attribute[$i],
                    'attribute_value_slug' => $attrValue,
                ]);
            }
        }
    }
}
