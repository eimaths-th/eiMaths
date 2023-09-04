<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CustomValidationRules implements Rule
{
    public function passes($attribute, $value)
    {
        $father_name = request()->input('father_name');
        $father_mobile = request()->input('father_mobile');
        $father_email = request()->input('father_email');
        $mother_name = request()->input('mother_name');
        $mother_mobile = request()->input('mother_mobile');
        $mother_email = request()->input('mother_email');

        if ($father_name!=="" ) {
            if ($father_mobile!=="" or $father_email!=="") {
                return true;
            } else {
                return false;
            }

        } elseif ($mother_name!=="" ) {
            if ($mother_mobile!=="" or $mother_email!=="") {
                return true;
            } else {
                return false;
            }

        } else {
            return false;
        }

        return false;
    }

    public function message()
    {
        return 'Custom validation failed.';
    }
}
