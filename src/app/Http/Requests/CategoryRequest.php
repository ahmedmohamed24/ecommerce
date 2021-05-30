<?php

namespace App\Http\Requests;

class CategoryRequest extends ApiFormRequest
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
            'name' => 'required|string|min:2|max:255',
            'details' => 'required|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:1024',
            'isBrand' => 'required|boolean',
        ];
    }
}
