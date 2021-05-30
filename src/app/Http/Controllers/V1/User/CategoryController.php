<?php

namespace App\Http\Controllers\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Traits\CustomUpload;
use App\Http\Traits\JsonResponse;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    use JsonResponse;
    use CustomUpload;
    const CATEGORIES_PER_PAGE = 20;

    public function getAll()
    {
        $categories = Category::paginate(self::CATEGORIES_PER_PAGE);

        return $this->response('success', 200, $categories);
    }

    public function show(string $slug)
    {
        try {
            $category = Category::where('slug', $slug)->firstOrFail();

            return $this->response('success', 200, $category);
        } catch (\Throwable $th) {
            return $this->notFoundReturn($th);
        }
    }

    public function getProducts(string $slug)
    {
        $validator = Validator::make(['slug' => $slug], ['slug' => 'string', 'max:255']);
        if ($validator->fails()) {
            return $this->response('error', 406, $validator->getMessageBag());
        }

        try {
            $category = Category::where('slug', $slug)->firstOrFail();
            $products = $category->products()->paginate(20);

            return $this->response('success', 200, $products);
        } catch (\Throwable $th) {
            return $this->notFoundReturn($th);
        }
    }
}
