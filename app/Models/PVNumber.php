<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $year
 * @property string $month
 * @property integer $number
 * @property string $created_at
 * @property string $updated_at
 */
class PVNumber extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'pv_running_number';

    /**
     * @var array
     */
    protected $fillable = ['year', 'month', 'number', 'created_at', 'updated_at'];
    
    public static function runningNumber()
    {
        $year = date('Y');
        $month = date('m');

        // Find or create current month/year record
        $record = self::firstOrCreate(
            ['year' => $year, 'month' => $month],
            ['number' => 0]
        );

        // Increment the number
        $record->increment('number');

        // Build the PV number
        $running = 'PV' . $year . $month . str_pad($record->number, 4, '0', STR_PAD_LEFT);

        return $running;
    }
}
