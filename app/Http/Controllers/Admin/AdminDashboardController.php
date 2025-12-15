<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Drone;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_drones' => Drone::count(),
            'total_users' => User::where('role', 'user')->count(),
            'total_bookings' => Booking::count(),
            'pending_bookings' => Booking::where('status', 'Pending')->count(),
            'low_stock_drones' => Drone::where('stock', '<=', 5)->count(),
            'total_revenue' => Booking::where('status', '!=', 'Cancelled')->sum('price'),
        ];

        $recent_bookings = Booking::with('user')
            ->orderBy('checkout_date', 'desc')
            ->limit(10)
            ->get();

        $low_stock_drones = Drone::where('stock', '<=', 5)
            ->orderBy('stock', 'asc')
            ->get();

        return view('admin.dashboard', compact('stats', 'recent_bookings', 'low_stock_drones'));
    }
}
