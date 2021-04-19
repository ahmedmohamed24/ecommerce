<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class ProductRequest extends FormRequest
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
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['errors' => $validator->errors()], Response::HTTP_NOT_ACCEPTABLE));
    }
}
