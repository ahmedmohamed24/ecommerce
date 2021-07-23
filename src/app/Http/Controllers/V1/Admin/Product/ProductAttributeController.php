<?php

namespace App\Http\Controllers\V1\Admin\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AttributeOptionRequest;
use App\Http\Requests\Admin\AttributeRequest;
use App\Http\Traits\JsonResponse;
use App\Models\Attribute;
use App\Models\AttributeOption;
use Illuminate\Support\Str;

class ProductAttributeController extends Controller
{
    use JsonResponse;

    public function storeAttribute(AttributeRequest $request)
    {
        $attribute = Attribute::create(['name' => $request->name, 'slug' => Str::slug($request->name)]);

        return $this->response('success', 201, $attribute);
    }

    public function storeOption(AttributeOptionRequest $request)
    {
        $option = AttributeOption::create(['name' => $request->name, 'slug' => Str::slug($request->name)]);

        return $this->response('success', 201, $option);
    }
}
