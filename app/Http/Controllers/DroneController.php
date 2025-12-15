<?php

namespace App\Http\Controllers;

use App\Models\Drone;
use Illuminate\Http\Request;

class DroneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Drone::query();

            // Filter by type if provided
            if ($request->has('type') && $request->type) {
                $query->where('type', $request->type);
            }

            // Search functionality
            if ($request->has('search') && $request->search) {
                $query->where(function($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('brand', 'like', '%' . $request->search . '%')
                      ->orWhere('description', 'like', '%' . $request->search . '%');
                });
            }

            $drones = $query->where('stock', '>', 0)->paginate(12);
            $types = Drone::distinct()->pluck('type');
        } catch (\Exception $e) {
            // If there's a database error, return empty collections
            $drones = \Illuminate\Pagination\LengthAwarePaginator::make([], 0, 12);
            $types = collect([]);
        }

        return view('drones.index', compact('drones', 'types'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Drone $drone)
    {
        return view('drones.show', compact('drone'));
    }
}
