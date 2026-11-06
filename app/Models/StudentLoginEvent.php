<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentLoginEvent extends Model
{
    public const LOGIN = 'LOGIN';
    public const LOGOUT = 'LOGOUT';

    public $timestamps = false;

    protected $fillable = ['contact_id', 'event_type', 'occurred_at'];

    protected $casts = ['occurred_at' => 'datetime'];
}
