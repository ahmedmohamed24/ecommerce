<?php

namespace App\Models;

use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory,SoftDeletes;

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
}
