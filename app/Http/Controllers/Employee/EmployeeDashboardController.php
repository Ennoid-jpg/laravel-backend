<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class EmployeeDashboardController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with('user');

        if ($request->has('search') && $request->search) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('FirstName', 'like', '%' . $request->search . '%')
                  ->orWhere('LastName', 'like', '%' . $request->search . '%')
                  ->orWhere('username', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('status') && $request->status) {
            $query->where('Status', $request->status);
        }

        $bookings = $query->orderBy('checkout_date', 'desc')->paginate(15);
        $statuses = ['Pending', 'Accepted', 'Returned', 'Returned Damaged', 'Cancelled'];

        $stats = [
            'total_bookings' => Booking::count(),
            'pending_bookings' => Booking::where('Status', 'Pending')->count(),
            'accepted_bookings' => Booking::where('Status', 'Accepted')->count(),
            'returned_bookings' => Booking::where('Status', 'Returned')->count(),
        ];

        return view('employee.dashboard', compact('bookings', 'statuses', 'stats'));
    }
}
