<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoursePending extends Model
{
    use HasFactory;

    // Define the table name if it's not the plural form of the model name
    protected $table = 'courses_pennding';  // Make sure the table name matches your database table

    // Specify the fillable fields for mass assignment
    protected $fillable = [
        'ref',
        'name',
        'email',
        'telp',
        'course_name',
        'course_id'
    ];

    // You can define relationships if needed
    // Example: public function course()
    // {
    //     return $this->belongsTo(Course::class);
    // }
}
