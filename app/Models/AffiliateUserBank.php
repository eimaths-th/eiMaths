<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $user_id
 * @property string $bank_name
 * @property string $account_number
 * @property string $account_name
 * @property string $created_at
 * @property string $updated_at
 */
class AffiliateUserBank extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'affiliate_user_banking';

    /**
     * @var array
     */
    protected $fillable = ['user_id', 'bank_name', 'account_number', 'account_name', 'created_at', 'updated_at'];
}
