<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Support\Branch\BranchNumber;

class ReceiptRunningNumber extends Model
{
    use HasFactory;
    protected $fillable = ['centre', 'year', 'month', 'number'];

    public static function pre_generate(string $department)
    {
        return BranchNumber::next(static::class, $department, 'centre', '', 3, true, true);
    }

    public static function generate(string $department)
    {
        return BranchNumber::next(static::class, $department, 'centre', '', 3, true, false);
    }
}
