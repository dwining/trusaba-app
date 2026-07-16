<?php

use Illuminate\Support\Facades\Route;

// Splash / Welcome
Route::get('/', function () {
    return view('splash');
})->name('splash');

// Auth routes (will be filled in Fase 1)
Route::get('/auth', function () {
    return view('auth.login');
})->name('auth');

// --- Authenticated routes (placeholder for now) ---
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // Onboarding
    Route::get('/onboarding', function () {
        return view('onboarding');
    })->name('onboarding');
    
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
