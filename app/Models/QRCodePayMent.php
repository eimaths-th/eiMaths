<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $qr_code
 * @property string $account_name
 * @property string $bank_name
 * @property string $account_numbers
 * @property string $promptpay_numbers
 * @property string $img
 * @property string $created_at
 * @property string $updated_at
 */
class QRCodePayMent extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'qr_payment';

    /**
     * @var array
     */
    protected $fillable = ['qr_code', 'account_name', 'bank_name', 'account_numbers', 'promptpay_numbers','status', 'img', 'created_at', 'updated_at'];
}
