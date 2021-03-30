<?php

namespace App\Http\Controllers;

use App\Http\Traits\JsonResponse;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class SubCategoryController extends Controller
{
    use JsonResponse;

    public function store(string $slug, Request $request)
    {
        //authorization

        //validate data
        $validator = Validator::make($request->only('slug'), [
            'slug' => ['required', 'string', 'max:255', "not_in:{$slug}"],
        ]);
        //$slug for master-category and $request->slug is sub-category
        if ($validator->fails()) {
            return $this->response('error', Response::HTTP_NOT_ACCEPTABLE, $validator->getMessageBag());
        }
        //validate the logic|  check if the two models exists
        $modelsNumber = Category::where('slug', $request->slug)->orWhere('slug', $slug)->get()->count();
        if (2 !== $modelsNumber) {//one for parent and one for child
            return $this->response('error', Response::HTTP_NOT_ACCEPTABLE, 'Data in not valid');
        }

        //presist
        SubCategory::create([
            'parent_cat' => $slug,
            'sub_cat' => $request->slug,
        ]);
        //response
        return $this->response('success', 200);
    }

    public function getSubCategories(string $slug)
    {
        try {
            $subCategories = Category::where('slug', $slug)->firstOrFail();

            return $this->response('success', 200, $subCategories->subCategories());
        } catch (\Throwable $th) {
            return $this->notFoundReturn($th);
        }
    }
}
