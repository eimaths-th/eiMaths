<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceRunningNumber extends Model
{
    use HasFactory;
    protected $fillable = ['centre', 'year', 'month', 'number'];

    public static function pre_generate(string $department)
    {

        $number = 0;
        $year = date('Y');
        $month = date('m');
        $type = strtoupper(Department::getDepartmentCodeById($department));


        if (!InvoiceRunningNumber::where('year', $year)->where('month', $month)->where('centre', $type)->exists()) {
            InvoiceRunningNumber::create([
                'number' => $number,
                'year' => $year,
                'month' => $month,
                'centre' => $type,
            ]);
        }

        $running_number = InvoiceRunningNumber::where('year', $year)->where('month', $month)->where('centre', $type)->first();

        $running_number->number++;
        //$running_number->save();
        $number = $running_number->number;
        $number = str_pad($number, 4, '0', STR_PAD_LEFT);


        return 'Inv-'.$type . '' . date('y') . date('m') . $number;
    }


    public static function generate(string $department)
    {
        $type = strtoupper(Department::getDepartmentCodeById($department));
        /*  if (! in_array($type, self::TYPES)) {
            throw new \Exception('Undefined running number type.');
        } */
        $number = 0;
        $year = date('Y');
        $month = date('m');
        if (!InvoiceRunningNumber::where('centre', $type)->where('year', $year)->where('month', $month)->exists()) {
            InvoiceRunningNumber::create([
                'centre' => $type,
                'number' => $number,
                'month' => $month,
                'year' => $year,
            ]);
        }

        $running_number = InvoiceRunningNumber::where('centre', $type)->where('year', $year)->where('month', $month)->first();
        $running_number->number++;
        $running_number->save();
        $number = $running_number->number;
        $number = str_pad($number, 4, '0', STR_PAD_LEFT);

        // A-21-00001
        return 'Inv-'.$type . '' . date('y') . date('m') . $number;
    }
}
