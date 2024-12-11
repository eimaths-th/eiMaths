<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * @property integer $id
 * @property string $line_user_id
 * @property string $channel_secret
 * @property string $channel_access_token
 * @property integer $create_by
 * @property integer $update_by
 * @property string $created_at
 * @property string $updated_at
 */
class LineApiSetting extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'line_api_use';

    /**
     * @var array
     */
    protected $fillable = ['name', 'line_user_id', 'channel_secret', 'channel_access_token', 'create_by', 'update_by', 'created_at', 'updated_at'];

    public function decrypt($value)
    {
        try {
            return $value ? Crypt::decryptString($value) : null;
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return null; // Or handle the error as required
        }
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'create_by');
    }
    public function editor()
    {
        return $this->belongsTo(User::class, 'update_by');
    }

   
}
