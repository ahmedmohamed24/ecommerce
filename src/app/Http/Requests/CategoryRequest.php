<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class CategoryRequest extends FormRequest
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
            'name'=>'required|string|min:2|max:255',
            'details'=>'required|string',
            'thumbnail'=>'nullable|image|mimes:jpeg,png,jpg|max:1024',
            'isBrand'=>'required|boolean'
        ];
    }
    /**
         * Handle a failed validation attempt.
         *
         * @param  \Illuminate\Contracts\Validation\Validator  $validator
         * @return void
         *
         * @throws \Illuminate\Validation\ValidationException
         */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['errors' => $validator->errors()], Response::HTTP_NOT_ACCEPTABLE));
    }
}
