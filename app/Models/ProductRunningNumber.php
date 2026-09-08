<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Support\Branch\BranchNumber;

class ProductRunningNumber extends Model
{
    use HasFactory;
    protected $fillable = ['department', 'number', 'year'];

    public static function pre_generate(string $department)
    {
        return BranchNumber::next(static::class, $department, 'department', 'AC-', 4, false, true);
    }

    public static function generate(string $department)
    {
        return BranchNumber::next(static::class, $department, 'department', 'AC-', 4, false, false);
    }
}
