<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ThaiNationalId implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $national_id = preg_replace('/\D+/', '', (string) $value);
        if (strlen($national_id) !== 13) {
            $fail(__('affiliate.identity.errors.national_id'));
            return;
        }

        $sum = 0;
        for ($index = 0; $index < 12; $index++) {
            $sum += ((int) $national_id[$index]) * (13 - $index);
        }
        $check_digit = (11 - ($sum % 11)) % 10;
        if ($check_digit !== (int) $national_id[12]) {
            $fail(__('affiliate.identity.errors.national_id'));
        }
    }
}
