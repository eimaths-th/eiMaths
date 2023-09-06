<?php

namespace App\Interfaces;

interface MoneyConvertServiceInterface
{
    public function baht_text($price);

    public function convertNumber($price);
}
