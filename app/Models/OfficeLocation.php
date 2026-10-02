<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_name',
        'latitude',
        'longitude',
        'radius_meters',
        'check_in_cutoff',
        'check_out_start_weekday',
        'check_out_start_friday',
        'check_out_start_weekend',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'radius_meters' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Dapatkan jam pulang minimal berdasarkan hari dalam seminggu
     */
    public function getCheckOutTimeForCarbon(Carbon $date): string
    {
        if ($date->isFriday()) {
            return $this->check_out_start_friday ?? '15:30:00';
        }

        if ($date->isWeekend()) {
            return $this->check_out_start_weekend ?? '16:00:00';
        }

        return $this->check_out_start_weekday ?? '16:00:00';
    }
}
