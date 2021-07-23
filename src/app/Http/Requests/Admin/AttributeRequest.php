<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiFormRequest;
use App\Models\Attribute;
use Illuminate\Support\Str;

class AttributeRequest extends ApiFormRequest
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
            'name' => ['string', 'max:255', function ($attribute, $value, $fail) {
                if (Attribute::where('slug', Str::slug($value))->count() > 0) {
                    $fail('The attribute name must be unique.');
                }
            }],
        ];
    }
}
