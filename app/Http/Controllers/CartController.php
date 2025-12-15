<?php

namespace App\Http\Controllers;

use App\Models\Drone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    public function index()
    {
        $cart = Session::get('cart', []);
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

        return view('cart.index', compact('cartItems', 'total'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'drone_id' => 'required|exists:drones,id_drone',
            'id_drone' => 'sometimes|exists:drones,id_drone',
            'quantity' => 'required|integer|min:1',
        ]);

        $droneId = $request->drone_id ?? $request->id_drone;
        $drone = Drone::findOrFail($droneId);

        if ($request->quantity > $drone->stock) {
            return back()->withErrors(['quantity' => 'Requested quantity exceeds available stock.']);
        }

        $cart = Session::get('cart', []);
        $id = $drone->id_drone;

        if (isset($cart[$id])) {
            $newQuantity = $cart[$id]['quantity'] + $request->quantity;
            if ($newQuantity > $drone->stock) {
                return back()->withErrors(['quantity' => 'Total quantity would exceed available stock.']);
            }
            $cart[$id]['quantity'] = $newQuantity;
        } else {
            $cart[$id] = [
                'quantity' => $request->quantity,
            ];
        }

        Session::put('cart', $cart);

        return redirect()->route('cart.index')->with('success', 'Drone added to cart!');
    }

    public function update(Request $request)
    {
        $request->validate([
            'quantities' => 'required|array',
            'quantities.*' => 'integer|min:1',
        ]);

        $cart = Session::get('cart', []);

        foreach ($request->quantities as $id => $quantity) {
            if (isset($cart[$id])) {
                $drone = Drone::find($id);
                if ($drone && $quantity <= $drone->stock) {
                    $cart[$id]['quantity'] = $quantity;
                }
            }
        }

        Session::put('cart', $cart);

        return redirect()->route('cart.index')->with('success', 'Cart updated!');
    }

    public function remove($id)
    {
        $cart = Session::get('cart', []);
        unset($cart[$id]);
        Session::put('cart', $cart);

        return redirect()->route('cart.index')->with('success', 'Item removed from cart!');
    }

    public function clear()
    {
        Session::forget('cart');
        return redirect()->route('cart.index')->with('success', 'Cart cleared!');
    }
}
