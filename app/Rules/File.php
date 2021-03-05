<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\UploadedFile;
use Throwable;

class File implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (is_string($value) && base64_encode(base64_decode($value, true)) === $value) {
            return true;
        }
        if ($value instanceof UploadedFile) {
            return true;
        }
        try {
            $contents = @file_get_contents($value);
            return is_string($contents);
        } catch (Throwable $e) {
            return false;
        }
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return ':attribute: is not a valid file.';
    }
}
