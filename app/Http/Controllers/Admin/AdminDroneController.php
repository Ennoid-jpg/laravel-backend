<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Drone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminDroneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Drone::query();

        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('brand', 'like', '%' . $request->search . '%')
                  ->orWhere('type', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        $drones = $query->orderBy('id_drone', 'desc')->paginate(12);
        $types = Drone::distinct()->pluck('type');

        return view('admin.drones.index', compact('drones', 'types'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.drones.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('drone-images', 'public');
            $validated['image'] = $path;
        }

        Drone::create($validated);

        return redirect()->route('admin.drones.index')
            ->with('success', 'Drone added successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Drone $drone)
    {
        return view('admin.drones.show', compact('drone'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Drone $drone)
    {
        return view('admin.drones.edit', compact('drone'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Drone $drone)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image
            if ($drone->image && Storage::disk('public')->exists($drone->image)) {
                Storage::disk('public')->delete($drone->image);
            }
            $path = $request->file('image')->store('drone-images', 'public');
            $validated['image'] = $path;
        } else {
            $validated['image'] = $drone->image;
        }

        $drone->update($validated);

        return redirect()->route('admin.drones.index')
            ->with('success', 'Drone updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Drone $drone)
    {
        // Delete image
        if ($drone->image && Storage::disk('public')->exists($drone->image)) {
            Storage::disk('public')->delete($drone->image);
        }

        $drone->delete();

        return redirect()->route('admin.drones.index')
            ->with('success', 'Drone deleted successfully!');
    }
}
