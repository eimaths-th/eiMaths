<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $first_name_parent
 * @property string $last_name_parent
 * @property string $telp_parent
 * @property string $email_parent
 * @property string $first_name_student
 * @property string $last_name_student
 * @property string $nick_name_student
 * @property string $category
 * @property string $address
 * @property string $created_at
 * @property string $updated_at
 */
class EiForm extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'ei_form';

    /**
     * @var array
     */
    protected $fillable = [
        'first_name_parent',
        'last_name_parent',
        'telp_parent',
        'email_parent',
        'first_name_student',
        'last_name_student',
        'nick_name_student',
        'category',
        'address',
        'created_at',
        'updated_at'
    ];
}
