<?php

namespace App\Http\Controllers\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Traits\JsonResponse;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    use JsonResponse;
    const PRODUCTS_PER_PAGE = 20;

    public function getAll(Request $request)
    {
        $page = $request->page ? $request->page : 1;
        $data = Cache::remember('products.'.$page, 60 * 60 * 30, function () {
            $paginatedProducts = (Product::paginate(self::PRODUCTS_PER_PAGE))->toArray();

            return [
                'data' => $paginatedProducts['data'],
                'total' => $paginatedProducts['total'],
                'per_page' => $paginatedProducts['per_page'],
                'from' => $paginatedProducts['from'],
                'to' => $paginatedProducts['to'],
                'current_page' => $paginatedProducts['current_page'],
                'next_page_url' => $paginatedProducts['next_page_url'],
                'prev_page_url' => $paginatedProducts['prev_page_url'],
                'first_page_url' => $paginatedProducts['first_page_url'],
                'last_page_url' => $paginatedProducts['last_page_url'],
            ];
        });

        return $this->response('success', 200, $data);
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
