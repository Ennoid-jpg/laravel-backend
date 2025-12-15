<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Drone extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_drone';
    public $incrementing = true;

    protected $fillable = [
        'name',
        'type',
        'image',
        'description',
        'price',
        'brand',
        'stock',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'id_drone', 'id_drone');
    }

    /**
     * Get the image URL with correct path
     */
    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return asset('images/drone.png'); // Default placeholder
        }

        // If image path starts with 'drones/', update it to 'drone-images/'
        $imagePath = str_replace('drones/', 'drone-images/', $this->image);
        
        // Check if file exists in public directory
        $publicPath = public_path($imagePath);
        if (file_exists($publicPath)) {
            return asset($imagePath);
        }

        // Fallback: try original path
        if (file_exists(public_path($this->image))) {
            return asset($this->image);
        }

        // Default placeholder if image doesn't exist
        return asset('images/drone.png');
    }
}
