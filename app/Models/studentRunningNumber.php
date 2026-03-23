<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Department;

class studentRunningNumber extends Model
{
    use HasFactory;
    protected $fillable = ['department', 'number', 'year'];

    public static function pre_generate(string $department)
    {

        $number = 0;
        $year = date('Y');
        $type = strtoupper(Department::getDepartmentCodeById($department));


        if (!studentRunningNumber::where('year', $year)->where('department', $type)->exists()) {
            // ดึงค่า number ล่าสุดจากปีก่อน (ถ้ามี) เพื่อต่อเนื่อง
            $lastRecord = studentRunningNumber::where('department', $type)
                ->orderBy('year', 'desc')
                ->orderBy('number', 'desc')
                ->first();
            
            $number = $lastRecord ? $lastRecord->number : 0;
            
            studentRunningNumber::create([
                'number' => $number,
                'year' => $year,
                'department' => $type,
            ]);
        }

        $running_number = studentRunningNumber::where('year', $year)->where('department', $type)->first();

        $running_number->number++;
        //$running_number->save();
        $number = $running_number->number;
        $number = str_pad($number, 4, '0', STR_PAD_LEFT);


        //return $type.''.$number;
        return $type . '' .$number;


    }


    public static function generate(string $department)
    {
        $type = strtoupper(Department::getDepartmentCodeById($department));
       /*  if (! in_array($type, self::TYPES)) {
            throw new \Exception('Undefined running number type.');
        } */
        $number = 0;
        $year = date('Y');
        if (! studentRunningNumber::where('department', $type)->where('year', $year)->exists()) {
            // ดึงค่า number ล่าสุดจากปีก่อน (ถ้ามี) เพื่อต่อเนื่อง
            $lastRecord = studentRunningNumber::where('department', $type)
                ->orderBy('year', 'desc')
                ->orderBy('number', 'desc')
                ->first();
            
            $number = $lastRecord ? $lastRecord->number : 0;
            
            studentRunningNumber::create([
                'department' => $type,
                'number' => $number,
                'year' => $year,
            ]);
        }

        $running_number = studentRunningNumber::where('department', $type)->where('year', $year)->first();
        $running_number->number++;
        $running_number->save();
        $number = $running_number->number;
        $number = str_pad($number, 4, '0', STR_PAD_LEFT);

        // A-21-00001
        //return $type.''.$number;
        return $type . '' .$number;
    }
}
