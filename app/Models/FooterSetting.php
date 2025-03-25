<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $title
 * @property string $description
 * @property string $description2
 * @property string $created_by
 * @property string $updated_by
 * @property string $created_at
 * @property string $updated_at
 */
class FooterSetting extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'footer_setting';

    /**
     * @var array
     */
    protected $fillable = ['title', 'description', 'description2', 'created_by', 'updated_by', 'created_at', 'updated_at'];
}
