<?php

namespace App\Http\Requests;

class OrderRequest extends ApiFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return \auth()->guard('api')->user();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'fullName' => 'required|string|min:2|max:255',
            'mobile' => 'required|numeric',
            'postal_code' => 'required|numeric',
            'address' => 'required|string',
            'shipping' => 'required|string|max:255',
            'paymentMethod' => 'required|string|in:"paypal","stripe","cashe"',
            //every cart has many items
            //every item is array ex: ['product'=>'mobile','stock'=>1,'attributes'=>['ram'=>'8G','color'=>'red']];
            'cart' => ['required', 'array', 'max:30'], //max products per cart
            'cart.*' => ['required', 'array'],
            'cart.*.product' => ['required', 'string', 'max:255',  'exists:products,slug'],
            'cart.*.stock' => ['required', 'numeric', 'min:1'],
            'cart.*.attributes' => ['nullable', 'array'],
            'cart.*.attributes.*' => ['required', 'string', 'max:255',  'exists:attributes,slug'], //attribute key
            'cart.*.attributes.*.*' => ['required', 'string', 'max:255', 'exists:attribute_options,slug'], //attribute key
        ];
    }
}
