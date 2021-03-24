<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Traits\JsonResponse;
use Intervention\Image\Facades\Image;
use App\Http\Requests\CategoryRequest;
use App\Http\Traits\CustomUpload;

use function PHPUnit\Framework\isEmpty;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    use JsonResponse,CustomUpload;
    const CATEGORIES_PER_PAGE=20;
    public function getAll()
    {
        $categories=Category::paginate(self::CATEGORIES_PER_PAGE);
        return $this->response('success', 200, $categories);
    }
    public function getTrashed()
    {
        $categories=Category::onlyTrashed()->paginate(self::CATEGORIES_PER_PAGE);
        return $this->response('success', 200, $categories);
    }
    public function store(CategoryRequest $request)
    {
        //validate name is unique
        $old=Category::where('name', $request->name)->get();
        if (!$old->isEmpty()) {
            return $this->response('error', Response::HTTP_NOT_ACCEPTABLE, ['errors'=>['name'=>'this name is not available.']]);
        }
        //if image exists then upload
        $img=$this->upload($request->file('thumbnail'), 'category');
        //presist
        $category=Category::create([
            'name'=>$request->name,
            'slug'=>Str::slug($request->name),
            'details'=>$request->details,
            'thumbnail'=>$img,
            'isBrand'=>$request->isBrand,
            ]);
        //response
        return $this->response('success', 200, $category);
    }
    public function show(string $slug)
    {
        try {
            $category=Category::where('slug', $slug)->firstOrFail();
            return $this->response('success', 200, $category);
        } catch (\Throwable $th) {
            return $this->notFoundReturn($th);
        }
    }
    public function update(CategoryRequest $request)
    {
        try {
            //validate (not getting the same name for another category)
            $old=Category::where('name', $request->name)->whereNotEqual('slug', $request->slug)->get();
            if (!$old->isEmpty()) {
                return $this->response('error', Response::HTTP_NOT_ACCEPTABLE, ['errors'=>['name'=>'this name is not available.']]);
            }
            //update
            $category=Category::where('slug', $request->slug)->firstOrFail()->update([
                'name'=>$request->name,
                'slug'=>Str::slug($request->name),
                'details'=>$request->details,
                'thumbnail'=>$request->thumbnail,
                'isBrand'=>$request->isBrand,
            ]);
            return $this->response('success', 200, $category);
        } catch (\Throwable $th) {
            return $this->notFoundReturn($th);
        }
    }
    public function softDelete(string $slug)
    {
        try {
            $category=Category::where('slug', $slug)->firstOrFail()->delete();
            return $this->response('success', 200, $category);
        } catch (\Throwable $th) {
            return $this->notFoundReturn($th);
        }
    }
    public function hardDelete(string $slug)
    {
        try {
            $category=Category::where('slug', $slug)->firstOrFail()->forceDelete();
            return $this->response('success', 200, $category);
        } catch (\Throwable $th) {
            return $this->notFoundReturn($th);
        }
    }
    public function restore(string $slug)
    {
        try {
            $category=Category::onlyTrashed()->where('slug', $slug)->firstOrFail()->restore();
            return $this->response('success', 200, $category);
        } catch (\Throwable $th) {
            return $this->notFoundReturn($th);
        }
    }
    public function getProducts(string $slug)
    {
        $validator=Validator::make(['slug'=>$slug], ['slug'=>'string','max:255']);
        if ($validator->fails()) {
            return $this->response('error', 406, $validator->getMessageBag());
        }
        try {
            $category=Category::where('slug', $slug)->firstOrFail();
            $products=$category->products()->paginate(20);
            return $this->response('success', 200, $products);
        } catch (\Throwable $th) {
            return $this->notFoundReturn($th);
        }
    }
}
