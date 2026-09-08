<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Support\Branch\BranchNumber;

class OrderRunningNumber extends Model
{
    use HasFactory;
    protected $fillable = ['centre', 'year', 'month', 'number'];

    public static function pre_generate(string $department)
    {
        return BranchNumber::next(static::class, $department, 'centre', 'Or-', 4, true, true);
    }

    public static function generate(string $department)
    {
        return BranchNumber::next(static::class, $department, 'centre', 'Or-', 4, true, false);
    }
}
