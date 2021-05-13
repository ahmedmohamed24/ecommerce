<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Traits\CustomUpload;
use App\Http\Traits\JsonResponse;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    use JsonResponse;
    use CustomUpload;
    const CATEGORIES_PER_PAGE = 20;
    private array $productCategoriesRelations;

    public function __construct()
    {
        $this->productCategoriesRelations = [];
    }

    public function getAll()
    {
        $categories = Category::paginate(self::CATEGORIES_PER_PAGE);

        return $this->response('success', 200, $categories);
    }

    public function getTrashed()
    {
        $categories = Category::onlyTrashed()->paginate(self::CATEGORIES_PER_PAGE);

        return $this->response('success', 200, $categories);
    }

    public function store(CategoryRequest $request)
    {
        //validate name is unique
        $old = Category::where('name', $request->name)->get();
        if (!$old->isEmpty()) {
            return $this->response('error', Response::HTTP_NOT_ACCEPTABLE, ['errors' => ['name' => 'this name is not available.']]);
        }
        //if image exists then upload
        $img = $this->upload($request->file('thumbnail'), 'category');
        $category = Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'details' => $request->details,
            'thumbnail' => $img,
            'isBrand' => $request->isBrand,
        ]);
        //response
        return $this->response('success', 200, $category);
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

    public function update(string $slug, CategoryRequest $request)
    {
        //validate the slug is unique
        $newSlug = Str::slug($request->name);
        if ($newSlug !== $slug) {
            $isSlugTaken = Category::where('slug', $newSlug)->get();
            if (!$isSlugTaken) {
                return $this->response('error', Response::HTTP_NOT_ACCEPTABLE, ['errors' => ['name' => 'this name is not available.']]);
            }
        }

        try {
            $oldCategoryData = Category::where('slug', $slug)->firstOrFail();
            //1- save the relations
            $this->saveProductCategoryRelations($oldCategoryData);
            //2- remove the relations
            $this->removeProductCategoryRelations($oldCategoryData);
            //3- update the category
            $isUpdated = Category::where('slug', $slug)->update([
                'name' => $request->name,
                'slug' => $newSlug,
                'details' => $request->details,
                'thumbnail' => $request->thumbnail,
                'isBrand' => $request->isBrand,
            ]);
            //4- attach relations again
            $newCategory = Category::where('slug', $newSlug)->firstOrFail();
            $this->restoreProductCategoryRelations($newCategory);
            if ($isUpdated) {
                return $this->response('success', 200, $newCategory);
            }

            throw new \Exception($isUpdated);
        } catch (\Throwable $th) {
            return $this->notFoundReturn($th);
        }
    }

    public function restoreProductCategoryRelations(Category $category)
    {
        $category->products()->sync($this->productCategoriesRelations);
    }

    public function removeProductCategoryRelations(Category $category)
    {
        $category->products()->detach();
    }

    public function saveProductCategoryRelations(Category $category)
    {
        foreach ($category->products as $product) {
            \array_push($this->productCategoriesRelations, $product->slug);
        }
    }

    public function softDelete(string $slug)
    {
        try {
            $category = Category::where('slug', $slug)->firstOrFail()->delete();

            return $this->response('success', 200, $category);
        } catch (\Throwable $th) {
            return $this->notFoundReturn($th);
        }
    }

    public function hardDelete(string $slug)
    {
        try {
            $category = Category::where('slug', $slug)->firstOrFail();
            if (\count($category->subCategories()) > 0) {
                return $this->response('cannot delete a parent category of other categories', 400);
            }
            if (\count($category->products) > 0) {
                $products = $category->products;
                $category->products()->detach();
                foreach ($products as $product) {
                    $product->forceDelete();
                }
            }
            $category->forceDelete();

            return $this->response('success', 200, $category);
        } catch (\Throwable $th) {
            return $this->notFoundReturn($th);
        }
    }

    public function restore(string $slug)
    {
        try {
            $category = Category::onlyTrashed()->where('slug', $slug)->firstOrFail()->restore();

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
