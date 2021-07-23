<?php

namespace App\Rules;

use App\Models\Attribute;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Str;

class UniqueAttributeSlugRule implements Rule
{
    /**
     * Create a new rule instance.
     */
    public function __construct()
    {
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        Attribute::where('slug', Str::slug($value))->count() > 0 ? false : true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be unique.';
    }
}
