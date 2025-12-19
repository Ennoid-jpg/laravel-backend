<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FeedbackController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Test route
Route::get('/test-drones', function () {
    return 'Drones route test - If you see this, routing works!';
});

// Backward compatibility route for old Menu.php
Route::get('/Menu.php', function () {
    return view('welcome');
})->name('menu');

// Dashboard - redirects based on role
Route::get('/dashboard', function () {
    $user = auth()->user();
    
    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    } elseif ($user->role === 'employee') {
        return redirect()->route('employee.dashboard');
    } else {
        return view('dashboard');
    }
})->middleware(['auth', 'verified'])->name('dashboard');

// Public routes
Route::get('/feedback', [FeedbackController::class, 'index'])->name('feedback.index');

// Public Drone Routes - Can browse without authentication
// Test route first
Route::get('/drones-test-simple', function() {
    return 'Simple test - if you see this, routing works!';
});

// Try with a closure first to test
Route::get('/drones-test-closure', function() {
    return 'Drones test closure - routing works!';
});

// Actual drone routes - MUST be in this order (index before {drone})
Route::get('/drones', [\App\Http\Controllers\DroneController::class, 'index'])->name('drones.index');

Route::get('/drones/{drone}', [\App\Http\Controllers\DroneController::class, 'show'])->name('drones.show');

// Authenticated user routes
Route::middleware(['auth'])->group(function () {
    
    // Cart Routes - Require email verification
    Route::middleware(['verified'])->group(function () {
        Route::post('/cart/add', [\App\Http\Controllers\CartController::class, 'add'])->name('cart.add');
        Route::post('/cart/update', [\App\Http\Controllers\CartController::class, 'update'])->name('cart.update');
        Route::get('/cart/remove/{id}', [\App\Http\Controllers\CartController::class, 'remove'])->name('cart.remove');
        Route::get('/cart/clear', [\App\Http\Controllers\CartController::class, 'clear'])->name('cart.clear');
        Route::get('/cart', [\App\Http\Controllers\CartController::class, 'index'])->name('cart.index');
        
        // Booking Routes - Require email verification
        Route::resource('bookings', \App\Http\Controllers\BookingController::class)->only(['index', 'show', 'create', 'store']);
    });
    
    // Feedback Routes
    Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');
});

// Profile Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('drones', \App\Http\Controllers\Admin\AdminDroneController::class);
    Route::resource('users', \App\Http\Controllers\Admin\AdminUserController::class);
    Route::resource('bookings', \App\Http\Controllers\Admin\AdminBookingController::class)->only(['index', 'create', 'store', 'show', 'update', 'destroy']);
    Route::delete('/feedback/{feedback}', [FeedbackController::class, 'destroy'])->name('feedback.destroy');
});

// Employee Routes
Route::middleware(['auth', 'employee'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Employee\EmployeeDashboardController::class, 'index'])->name('dashboard');
    Route::resource('bookings', \App\Http\Controllers\Employee\EmployeeBookingController::class)->only(['index', 'create', 'store', 'show', 'update']);
});

require __DIR__.'/auth.php';
