<?php

namespace App\Http\Controllers\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Traits\JsonResponse;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    use JsonResponse;
    const PRODUCTS_PER_PAGE = 20;
    const PRODUCTS_FOR_RECOMMENDATION = 10;
    private array $productCategoriesRelations;

    public function __construct()
    {
        $this->productCategoriesRelations = [];
    }

    public function getAll()
    {
        $products = Product::paginate(self::PRODUCTS_PER_PAGE);

        return $this->response('success', 200, $products);
    }

    public function getRandom()
    {
        $products = Product::inRandomOrder()->take(self::PRODUCTS_PER_PAGE)->get();

        return $this->response('success', 200, $products);
    }

    public function getTrashed()
    {
        $products = Product::onlyTrashed()->paginate(self::PRODUCTS_PER_PAGE);

        return $this->response('success', 200, $products);
    }

    public function store(ProductRequest $request)
    {
        try {
            DB::beginTransaction();
            //create the product, then attach it the the given categories
            $product = Product::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
                'details' => $request->details,
                'price' => $request->price,
                'owner' => \auth()->id(),
            ]);
            $product->categories()->attach($request->categories);
            DB::commit();

            return $this->response('created', 201, $product);
        } catch (\Throwable $th) {
            DB::rollBack();

            return $this->response('error', 406, $th->getMessage());
        }
    }

    public function show(string $slug)
    {
        try {
            $product = Product::where('slug', $slug)->with('categories')->firstOrFail();

            return $this->response('success', 200, ['product' => $product, 'recommended_products' => $product->recommendations()]);
        } catch (\Throwable $th) {
            //this message should be added to log and not sent to user
            return $this->notFoundReturn($th);
        }
    }

    public function update(string $slug, ProductRequest $request)
    {
        $oldProductData = Product::where('slug', $slug)->firstOrFail();
        $this->authorize('update', $oldProductData);
        //validate (not getting the same name for another category)
        $newSlug = Str::slug($request->name);
        if ($newSlug !== $slug) {
            $isSlugTaken = Product::where('slug', $newSlug)->get();
            if (!$isSlugTaken) {
                return $this->response('error', Response::HTTP_NOT_ACCEPTABLE, ['errors' => ['name' => 'this name is not available.']]);
            }
        }

        try {
            //1- save the relations
            $this->saveProductCategoryRelations($oldProductData);
            //2- remove the relations
            $this->removeProductCategoryRelations($oldProductData);
            //3- update the product
            $product = Product::where('slug', $slug);
            $isUpdated = $product->update([
                'name' => $request->name,
                'slug' => $newSlug,
                'description' => $request->description,
                'details' => $request->details,
                'price' => $request->price,
            ]);
            //4- attach relations again
            $newProduct = Product::where('slug', $newSlug)->firstOrFail();
            $this->restoreProductCategoryRelations($newProduct);
            if ($isUpdated) {
                return $this->response('updated', 200, $newProduct->toArray());
            }

            throw new \Exception($isUpdated);
        } catch (\Throwable $th) {
            return $this->notFoundReturn($th);
        }
    }

    public function restoreProductCategoryRelations(Product $product)
    {
        $product->categories()->sync($this->productCategoriesRelations);
    }

    public function removeProductCategoryRelations(Product $product)
    {
        $product->categories()->detach();
    }

    public function saveProductCategoryRelations(Product $product)
    {
        foreach ($product->categories as $category) {
            \array_push($this->productCategoriesRelations, $category->slug);
        }
    }

    public function destroy(string $slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();
        $this->authorize('delete', $product);

        try {
            $product = $product->delete();

            return $this->response('success', 200, $product);
        } catch (\Throwable $th) {
            return $this->notFoundReturn($th);
        }
    }

    public function restore(string $slug, Request $request)
    {
        $product = Product::onlyTrashed()->where('slug', $slug)->firstOrFail();
        $this->authorize('delete', $product);

        try {
            $product->restore();

            return $this->response('success', 200, $product);
        } catch (\Throwable $th) {
            return $this->notFoundReturn($th);
        }
    }

    public function getOwnerInfo(string $productSlug)
    {
        $owner = Product::with('getOwner')->where('slug', $productSlug)->firstOrFail()->getOwner;

        return $this->response('success', 200, ['vendor' => $owner, 'products' => $owner->products()->get()]);
    }
    public function search(string $query)
    {
        $searchResult = Product::search($query)->paginate(self::PRODUCTS_PER_PAGE);
        if (count($searchResult->items()) === 0) {
            return $this->response('Not found', 404);
        }
        return $this->response('success', 200, $searchResult);
    }
}
