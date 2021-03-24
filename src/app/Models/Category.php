<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory,SoftDeletes;
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
    /**
     *  return the path of the category either is id or slug or any unique value
     *
     * @return string
     */
    public function path()
    {
        return '/category/'.$this->slug;
    }
    public function subCategories()
    {
        $slug=$this->slug;
        return  DB::table('categories')->whereIn('slug', function ($query) use ($slug) {
            $query->select('sub_cat')->from('sub_categories')->where('parent_cat', $slug);
        })->get();
    }
    public function parentCategory()
    {
        $slug=$this->slug;
        return  DB::table('categories')->whereIn('slug', function ($query) use ($slug) {
            $query->select('parent_cat')->from('sub_categories')->where('sub_cat', $slug);
        })->get();
    }
    public function products()
    {
        return $this->belongsToMany(Product::class, 'category_product', 'category_slug', 'product_slug', 'slug', 'slug');
    }
}
