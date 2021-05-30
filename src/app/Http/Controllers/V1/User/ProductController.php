<?php

namespace App\Http\Controllers\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Traits\JsonResponse;
use App\Models\Product;

class ProductController extends Controller
{
    use JsonResponse;
    const PRODUCTS_PER_PAGE = 20;

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

    public function show(string $slug)
    {
        try {
            $product = Product::where('products.slug', $slug)->with('categories', 'attributes')->firstOrFail();
            return $this->response('success', 200, ['product' => $product, 'recommended_products' => $product->recommendations()]);
        } catch (\Throwable $th) {
            //this message should be added to log and not sent to user
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
        if (0 === count($searchResult->items())) {
            return $this->response('Not found', 404);
        }

        return $this->response('success', 200, $searchResult);
    }
}
