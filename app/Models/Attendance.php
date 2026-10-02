<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'check_in_time',
        'check_out_time',
        'check_in_photo',
        'check_out_photo',
        'check_in_lat',
        'check_in_long',
        'check_out_lat',
        'check_out_long',
        'distance_in_meters',
        'distance_out_meters',
        'status',
        'late_minutes',
        'is_mock_location',
        'gps_accuracy',
        'is_suspicious',
        'security_note',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'check_in_lat' => 'float',
        'check_in_long' => 'float',
        'check_out_lat' => 'float',
        'check_out_long' => 'float',
        'distance_in_meters' => 'integer',
        'distance_out_meters' => 'integer',
        'late_minutes' => 'integer',
        'is_mock_location' => 'boolean',
        'gps_accuracy' => 'float',
        'is_suspicious' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
