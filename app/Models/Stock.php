<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'centre', 'product_id', 'type', 'user_id', 'student_id', 'add_stock', 'rm_stock', 'in_stock', 'remark', 'created_at', 'updated_at'
    ];


    public function history()
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }
}
