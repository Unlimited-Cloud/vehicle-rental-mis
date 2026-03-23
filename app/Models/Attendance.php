<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table = 'attendance';

    protected $fillable = [
        'crew_id',
        'booking_id',
        'vehicle_moment_id',
        'attendance_date',
        'salary_amount',
        'bonus',
        'deduction',
        'status',
        'remarks',
    ];

    protected $casts = [
        'attendance_date' => 'date',  // This will convert to Carbon instance
        'salary_amount' => 'decimal:2',
        'bonus' => 'decimal:2',
        'deduction' => 'decimal:2',
        'net_amount' => 'decimal:2'
    ];

    public function booking()
    {
        return $this->belongsTo(VehicleBooking::class, 'booking_id');
    }

    public function vehicleMoment()
    {
        return $this->belongsTo(VehicleMoment::class, 'vehicle_moment_id');
    }
    public function crew()
    {
        return $this->belongsTo(CrewProfile::class);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'present' => '<span class="badge badge-success">Present</span>',
            'absent' => '<span class="badge badge-danger">Absent</span>',
            'half_day' => '<span class="badge badge-warning">Half Day</span>',
            'holiday' => '<span class="badge badge-info">Holiday</span>',
            'leave' => '<span class="badge badge-secondary">Leave</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge badge-secondary">Unknown</span>';
    }
}
