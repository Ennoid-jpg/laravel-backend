<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $table = 'booked';
    protected $primaryKey = 'id_booked';
    public $incrementing = true;

    protected $fillable = [
        'id_user',
        'id_drone',
        'price',
        'return_date',
        'return_time',
        'payment_type',
        'checkout_date',
        'receiver_name',
        'Status',
        'item_names',
        'quantity',
        'item_quantities',
    ];

    protected $casts = [
        'return_date' => 'date',
        'return_time' => 'datetime',
        'checkout_date' => 'datetime',
        'price' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    /**
     * Get the drone(s) for this booking.
     * Note: id_drone might be comma-separated for multiple drones
     */
    public function getDroneAttribute()
    {
        // If id_drone is comma-separated, get the first one
        $droneId = is_string($this->id_drone) && strpos($this->id_drone, ',') !== false 
            ? explode(',', $this->id_drone)[0] 
            : $this->id_drone;
        
        return Drone::where('id_drone', $droneId)->first();
    }

    /**
     * Get all drones if id_drone is comma-separated
     */
    public function getDronesAttribute()
    {
        if (is_string($this->id_drone) && strpos($this->id_drone, ',') !== false) {
            $droneIds = array_filter(explode(',', $this->id_drone));
            return Drone::whereIn('id_drone', $droneIds)->get();
        }
        return collect([$this->drone])->filter();
    }
}
