<?php

namespace App\Support\Recaptcha;

enum RecaptchaResult: string
{
    case PASSED = 'PASSED';
    case REJECTED = 'REJECTED';
    case UNAVAILABLE = 'UNAVAILABLE';
}
