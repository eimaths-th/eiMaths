<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductRunningNumber extends Model
{
    use HasFactory;
    protected $fillable = ['department', 'number', 'year'];

    public static function pre_generate(string $department)
    {

        $number = 0;
        $year = date('Y');
        $type = strtoupper(Department::getDepartmentCodeById($department));


        if (!ProductRunningNumber::where('year', $year)->where('department', $type)->exists()) {
            ProductRunningNumber::create([
                'number' => $number,
                'year' => $year,
                'department' => $type,
            ]);
        }

        $running_number = ProductRunningNumber::where('year', $year)->where('department', $type)->first();

        $running_number->number++;
        //$running_number->save();
        $number = $running_number->number;
        $number = str_pad($number, 4, '0', STR_PAD_LEFT);


        //return $type.''.$number;
        return 'AC-'.$type . '' .$number;


    }


    public static function generate(string $department)
    {
        $type = strtoupper(Department::getDepartmentCodeById($department));
       /*  if (! in_array($type, self::TYPES)) {
            throw new \Exception('Undefined running number type.');
        } */
        $number = 0;
        $year = date('Y');
        if (! ProductRunningNumber::where('department', $type)->where('year', $year)->exists()) {
            ProductRunningNumber::create([
                'department' => $type,
                'number' => $number,
                'year' => $year,
            ]);
        }

        $running_number = ProductRunningNumber::where('department', $type)->where('year', $year)->first();
        $running_number->number++;
        $running_number->save();
        $number = $running_number->number;
        $number = str_pad($number, 4, '0', STR_PAD_LEFT);

        // A-21-00001
        //return $type.''.$number;
        return 'AC-'.$type . '' .$number;
    }
}
