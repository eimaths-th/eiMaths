<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductRunningNumber extends Model
{
    use HasFactory;
    protected $fillable = ['number', 'year', 'month'];

    public static function pre_generate()
    {

        $number = 0;
        $year = date('Y');
        $month = date('m');


        if (!ProductRunningNumber::where('year', $year)->where('month', $month)->exists()) {
            ProductRunningNumber::create([
                'number' => $number,
                'year' => $year,
                'month' => $month,
            ]);
        }

        $running_number = ProductRunningNumber::where('year', $year)->where('month', $month)->first();

        $running_number->number++;
        //$running_number->save();
        $number = $running_number->number;
        $number = str_pad($number, 3, '0', STR_PAD_LEFT);


        return  'PR'.date('y') . '' . date('m') . '' . $number;


    }

    public static function generate()
    {

        $number = 0;
        $year = date('Y');
        $month = date('m');
        //$month = "01";

        if (!ProductRunningNumber::where('year', $year)->where('month', $month)->exists()) {
            ProductRunningNumber::create([
                'number' => $number,
                'year' => $year,
                'month' => $month,
            ]);
        }

        $running_number = ProductRunningNumber::where('year', $year)->where('month', $month)->first();

        $running_number->number++;
        $running_number->save();
        $number = $running_number->number;
        $number = str_pad($number, 3, '0', STR_PAD_LEFT);

        return 'PR'.date('y') . '' . date('m') . '' . $number;

    }
}
