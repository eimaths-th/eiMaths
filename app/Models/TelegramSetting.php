<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramSetting extends Model
{
    use HasFactory;

    protected $table = 'telegram_settings';

    protected $fillable = [
        'bot_token',
        'chat_id',
        'status',
        'description',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Get active Telegram setting
     */
    public static function getActive()
    {
        return self::where('status', 1)->first();
    }
}
