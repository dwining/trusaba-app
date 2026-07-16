<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Splash
Route::get('/', function () {
    return view('splash');
})->name('splash');

// Auth routes (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/auth', function () {
        return view('auth.login');
    })->name('auth');

    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login');

    // Google OAuth
    Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');
});

// Logout (any authenticated user)
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Onboarding / Profile
    Route::get('/onboarding', function () {
        return view('onboarding');
    })->name('onboarding');

    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Itinerary
    Route::get('/itineraries', function () {
        return view('itinerary.index');
    })->name('itineraries.index');

    Route::get('/itineraries/{id}', function () {
        return view('itinerary.show');
    })->name('itineraries.show');

    // Booking
    Route::get('/bookings', function () {
        return view('booking.index');
    })->name('bookings.index');

    // Today
    Route::get('/today', function () {
        return view('today');
    })->name('today');

    // Chat
    Route::get('/chat', function () {
        return view('chat');
    })->name('chat');

    // History
    Route::get('/history', function () {
        return view('history');
    })->name('history');

    // Upload
    Route::get('/expenses/upload', function () {
        return view('expenses.upload');
    })->name('expenses.upload');
});
