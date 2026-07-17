<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ExpenseUploadController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\ItineraryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SosController;
use App\Http\Controllers\TodayController;
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
    Route::get('/itineraries', [ItineraryController::class, 'index'])->name('itineraries.index');
    Route::post('/itineraries/generate', [ItineraryController::class, 'generate'])->name('itineraries.generate');
    Route::get('/itineraries/{id}/loading', [ItineraryController::class, 'loading'])->name('itineraries.loading');
    Route::get('/itineraries/{id}/status', [ItineraryController::class, 'status'])->name('itineraries.status');
    Route::get('/itineraries/{id}', [ItineraryController::class, 'show'])->name('itineraries.show');
    Route::put('/itineraries/{id}', [ItineraryController::class, 'update'])->name('itineraries.update');
    Route::delete('/itineraries/{id}', [ItineraryController::class, 'destroy'])->name('itineraries.destroy');

    // Booking
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
    Route::get('/bookings/create/payment', [BookingController::class, 'createPayment'])->name('bookings.create.payment');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{id}', [BookingController::class, 'show'])->name('bookings.show');
    Route::get('/bookings/{id}/payment', [BookingController::class, 'payment'])->name('bookings.payment');
    Route::get('/bookings/{id}/success', [BookingController::class, 'success'])->name('bookings.success');

    // Today
    Route::get('/today', [TodayController::class, 'index'])->name('today');

    // Chat
    Route::get('/chat', [ChatController::class, 'index'])->name('chat');
    Route::post('/chat/send', [ChatController::class, 'send'])->name('chat.send');

    // SOS
    Route::post('/sos', [SosController::class, 'send'])->name('sos.send');

    // History
    Route::get('/history', [HistoryController::class, 'index'])->name('history');

    // Upload
    Route::get('/expenses/upload', [ExpenseUploadController::class, 'index'])->name('expenses.upload');
    Route::post('/expenses/upload', [ExpenseUploadController::class, 'store'])->name('expenses.store');
});
