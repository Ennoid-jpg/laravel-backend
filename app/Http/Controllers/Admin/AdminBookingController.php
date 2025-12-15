<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Drone;
use Illuminate\Http\Request;

class AdminBookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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
            $query->where('status', $request->status);
        }

        $bookings = $query->orderBy('checkout_date', 'desc')->paginate(15);
        $statuses = ['Pending', 'Accepted', 'Returned', 'Returned Damaged', 'Cancelled'];

        return view('admin.bookings.index', compact('bookings', 'statuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = \App\Models\User::where('role', 'user')->orderBy('FirstName')->get();
        $drones = Drone::where('stock', '>', 0)->orderBy('name')->get();
        return view('admin.bookings.create', compact('users', 'drones'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_user' => 'required|exists:users,id_user',
            'drone_ids' => 'required|array|min:1',
            'drone_ids.*' => 'exists:drones,id_drone',
            'quantities' => 'required|array|min:1',
            'quantities.*' => 'required|integer|min:1',
            'return_date' => 'required|date|after:today',
            'return_time' => 'required',
            'payment_type' => 'required|string|max:255',
            'receiver_name' => 'nullable|string|max:255',
        ]);

        // Validate quantities match drone_ids
        if (count($validated['drone_ids']) !== count($validated['quantities'])) {
            return back()->withErrors(['quantities' => 'Each drone must have a quantity.'])->withInput();
        }

        // Check stock availability
        $itemNames = [];
        $itemQuantities = [];
        $totalPrice = 0;
        $droneIds = [];

        foreach ($validated['drone_ids'] as $index => $droneId) {
            $drone = Drone::find($droneId);
            if (!$drone) {
                return back()->withErrors(['drone_ids' => 'One or more drones not found.'])->withInput();
            }

            $quantity = (int)$validated['quantities'][$index];
            if ($drone->stock < $quantity) {
                return back()->withErrors(['quantities' => "Insufficient stock for {$drone->name}. Only {$drone->stock} available."])->withInput();
            }

            $itemNames[] = $drone->name;
            $itemQuantities[] = $quantity;
            $totalPrice += $drone->price * $quantity;
            $droneIds[] = $droneId;
        }

        // Get receiver name or use user's name
        $user = \App\Models\User::find($validated['id_user']);
        $receiverName = $validated['receiver_name'] ?? ($user->FirstName . ' ' . $user->LastName);

        // Create booking
        $booking = Booking::create([
            'id_user' => $validated['id_user'],
            'id_drone' => implode(',', $droneIds),
            'price' => $totalPrice,
            'return_date' => $validated['return_date'],
            'return_time' => $validated['return_time'],
            'payment_type' => $validated['payment_type'],
            'checkout_date' => now(),
            'receiver_name' => $receiverName,
            'Status' => 'Pending',
            'item_names' => implode(', ', $itemNames),
            'item_quantities' => implode(', ', $itemQuantities),
            'quantity' => array_sum($itemQuantities),
        ]);

        // Update stock
        foreach ($validated['drone_ids'] as $index => $droneId) {
            $drone = Drone::find($droneId);
            if ($drone) {
                $drone->decrement('stock', (int)$validated['quantities'][$index]);
            }
        }

        return redirect()->route('admin.bookings.index')
            ->with('success', 'Booking created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Booking $booking)
    {
        $booking->load('user');
        return view('admin.bookings.show', compact('booking'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'status' => 'required|in:Pending,Accepted,Returned,Returned Damaged,Cancelled',
        ]);

        $oldStatus = $booking->Status;
        $booking->update(['Status' => $validated['status']]);

        // If status changed to Returned or Returned Damaged, restore stock
        if (in_array($validated['status'], ['Returned', 'Returned Damaged']) && $oldStatus !== $validated['status']) {
            $droneIds = explode(',', $booking->id_drone);
            $quantities = $booking->item_quantities ? explode(',', $booking->item_quantities) : [1];

            foreach ($droneIds as $index => $droneId) {
                $drone = Drone::find(trim($droneId));
                if ($drone) {
                    $quantity = isset($quantities[$index]) ? (int)trim($quantities[$index]) : 1;
                    $drone->increment('stock', $quantity);
                }
            }
        }

        return redirect()->route('admin.bookings.index')
            ->with('success', 'Booking status updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Booking $booking)
    {
        $booking->delete();

        return redirect()->route('admin.bookings.index')
            ->with('success', 'Booking deleted successfully!');
    }
}
