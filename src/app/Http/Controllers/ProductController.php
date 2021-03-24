<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use function PHPSTORM_META\type;
use App\Http\Traits\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ProductRequest;

use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductController extends Controller
{
    use JsonResponse;
    const PRODUCTS_PER_PAGE=20;
    public function getAll()
    {
        $products=Product::paginate(self::PRODUCTS_PER_PAGE);
        return $this->response('success', 200, $products);
    }
    public function getRandom()
    {
        $products=Product::inRandomOrder()->take(self::PRODUCTS_PER_PAGE)->get();
        return $this->response('success', 200, $products);
    }
    public function getTrashed()
    {
        $products=Product::onlyTrashed()->paginate(self::PRODUCTS_PER_PAGE);
        return $this->response('success', 200, $products);
    }
    public function store(ProductRequest $request)
    {
        try {
            DB::beginTransaction();
            //create the product then attach it the the given categories
            $product=Product::create([
                'name'=>$request->name,
                'slug'=>Str::slug($request->name),
                'description'=>$request->description,
                'details'=>$request->details,
                'price'=>$request->price
            ]);
            foreach ($request->categories as $category) {
                DB::table('category_product')->insert([
                    'product_slug'=> $product->slug,
                    'category_slug'=>$category //array of slugs in the request
                ]);
            }
            DB::commit();
            return $this->response('success', Response::HTTP_OK, $product);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->response('error', 406, $th->getMessage());
        }
    }
    public function show(string $slug)
    {
        try {
            $product=Product::where('slug', $slug)->firstOrFail();
            return $this->response('success', 200, $product);
        } catch (\Throwable $th) {
            //this message should be added to log and not sent to user
            return $this->notFoundReturn($th);
        }
    }
    public function update(ProductRequest $request)
    {
        try {
            $product=Product::where('slug', $request->slug)->update([
                'name'=>$request->name,
                'slug'=>Str::slug($request->name),
                'description'=>$request->description,
                'details'=>$request->details,
                'price'=>$request->price
            ]);
            return $this->response('success', 200, $product);
        } catch (\Throwable $th) {
            return $this->notFoundReturn($th);
        }
    }
    public function destory(string $slug)
    {
        try {
            $product=Product::where('slug', $slug)->firstOrFail()->delete();
            return $this->response('success', 200, $product);
        } catch (\Throwable $th) {
            return $this->notFoundReturn($th);
        }
    }
    public function restore(string $slug, Request $request)
    {
        try {
            $product=Product::withTrashed()->where('slug', $slug)->firstOrFail()->restore();
            $this->response('success', 200, $product);
        } catch (\Throwable $th) {
            return $this->notFoundReturn($th);
        }
    }
}
