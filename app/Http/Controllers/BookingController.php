<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Drone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Auth::user()->bookings()->with('drone')->latest()->paginate(10);
        return view('bookings.index', compact('bookings'));
    }

    public function create()
    {
        $cart = Session::get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Your cart is empty.']);
        }

        $cartItems = [];
        $total = 0;

        foreach ($cart as $id => $item) {
            $drone = Drone::find($id);
            if ($drone) {
                $item['drone'] = $drone;
                $item['subtotal'] = $drone->price * $item['quantity'];
                $total += $item['subtotal'];
                $cartItems[] = $item;
            }
        }

        return view('bookings.create', compact('cartItems', 'total'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'return_date' => 'required|date|after:today',
            'return_time' => 'required',
            'card_number' => 'required|digits:16',
            'expiration_date' => 'required',
            'cvv' => 'required|digits:3',
        ]);

        $cart = Session::get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Your cart is empty.']);
        }

        $user = Auth::user();
        $itemNames = [];
        $itemQuantities = [];
        $totalPrice = 0;
        $droneIds = [];

        foreach ($cart as $id => $item) {
            $drone = Drone::find($id);
            if ($drone) {
                $quantity = $item['quantity'];
                $itemNames[] = $drone->name;
                $itemQuantities[] = $quantity;
                $totalPrice += $drone->price * $quantity;
                $droneIds[] = $id;
            }
        }

        // Create booking
        $booking = Booking::create([
            'id_user' => $user->id_user,
            'id_drone' => implode(',', $droneIds),
            'price' => $totalPrice,
            'return_date' => $request->return_date,
            'return_time' => $request->return_time,
            'payment_type' => 'Credit Card',
            'checkout_date' => now(),
            'receiver_name' => $user->FirstName . ' ' . $user->LastName,
            'Status' => 'Pending',
            'item_names' => implode(', ', $itemNames),
            'item_quantities' => implode(', ', $itemQuantities),
            'quantity' => array_sum($itemQuantities),
        ]);

        // Update stock
        foreach ($cart as $id => $item) {
            $drone = Drone::find($id);
            if ($drone) {
                $drone->decrement('stock', $item['quantity']);
            }
        }

        // Clear cart
        Session::forget('cart');

        return redirect()->route('bookings.index')->with('success', 'Booking created successfully!');
    }

    public function show($id)
    {
        $booking = Booking::with('user')->findOrFail($id);
        return view('bookings.show', compact('booking'));
    }
}
