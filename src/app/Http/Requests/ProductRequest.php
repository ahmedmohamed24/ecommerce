<?php

namespace App\Http\Requests;

class ProductRequest extends ApiFormRequest
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
            'name' => 'string|required|min:2|max:255',
            'description' => 'required|string|min:2',
            'details' => 'required|string|min:2',
            'categories' => 'required|array',
            'categories.*' => 'string|exists:categories,slug',
            'price' => 'required|numeric|min:1|max:999999.99',
            'attribute' => ['nullable', 'array'],
            'attribute.*' => ['string', 'exists:attributes,slug'],
            'attributesValues' => ['nullable', 'array'],
            'attributesValues.*' => ['array'],
            'attributesValues.*.*' => ['string', 'exists:attribute_options,slug'],
        ];
    }
}
