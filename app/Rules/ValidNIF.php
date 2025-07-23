<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidNIF implements Rule
{
    public function passes($attribute, $value)
    {
        $value = strtoupper(trim($value));
        // Check format: 8 digits + 1 letter
        if (!preg_match('/^[0-9]{8}[A-Z]$/', $value)) {
            return false;
        }
        $numbers = substr($value, 0, 8);
        $letter = substr($value, -1);
        $letters = 'TRWAGMYFPDXBNJZSQVHLCKE';

        // Calculate checksum
        $index = intval($numbers) % 23;
        return $letter === $letters[$index];
    }

    public function message()
    {
        return 'The :attribute must be a valid NIF.';
    }
}
