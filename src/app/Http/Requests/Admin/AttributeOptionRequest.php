<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;
use App\Models\AttributeOption;
use Illuminate\Support\Str;

class AttributeOptionRequest extends ApiFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['string', 'max:255', function ($attr, $val, $fail) {
                if (AttributeOption::where('slug', Str::slug($val))->count() > 0) {
                    $fail('The attribute option must be unique.');
                }
            }],
        ];
    }
}
