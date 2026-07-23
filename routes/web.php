<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\Chat\PrivateChatController;
use App\Http\Controllers\Chat\RequestController;
use App\Http\Controllers\Chat\RoomController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ExpenseUploadController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\ItineraryController;
use App\Http\Controllers\ItineraryItemController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SosController;
use App\Http\Controllers\TodayController;
use Illuminate\Support\Facades\Route;

// Splash
Route::get('/', function () {
    return view('splash');
})->name('splash');

// Redirect /login to /auth
Route::redirect('/login', '/auth');
Route::redirect('/register', '/auth');
Route::redirect('/profile', '/profile/edit');

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

    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Itinerary
    Route::get('/itineraries', [ItineraryController::class, 'index'])->name('itineraries.index');
    Route::post('/itineraries/generate', [ItineraryController::class, 'generate'])->name('itineraries.generate');
    Route::get('/itineraries/{id}/loading', [ItineraryController::class, 'loading'])->name('itineraries.loading');
    Route::get('/itineraries/{id}/status', [ItineraryController::class, 'status'])->name('itineraries.status');
    Route::get('/itineraries/{id}', [ItineraryController::class, 'show'])->name('itineraries.show');
    Route::put('/itineraries/{id}', [ItineraryController::class, 'update'])->name('itineraries.update');
    Route::delete('/itineraries/{id}', [ItineraryController::class, 'destroy'])->name('itineraries.destroy');

    // Itinerary item CRUD (edit mode)
    Route::post('/itineraries/{itinerary}/items', [ItineraryItemController::class, 'store'])->name('itinerary-items.store');
    Route::put('/itineraries/{itinerary}/items/{item}', [ItineraryItemController::class, 'update'])->name('itinerary-items.update');
    Route::delete('/itineraries/{itinerary}/items/{item}', [ItineraryItemController::class, 'destroy'])->name('itinerary-items.destroy');

    // Booking
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
    Route::get('/bookings/create/payment', [BookingController::class, 'createPayment'])->name('bookings.create.payment');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{id}', [BookingController::class, 'show'])->name('bookings.show');
    Route::get('/bookings/{id}/payment', [BookingController::class, 'payment'])->name('bookings.payment');
    Route::get('/bookings/{id}/success', [BookingController::class, 'success'])->name('bookings.success');

    // Cart
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::delete('/cart/{id}', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');

    // Today
    Route::get('/today', [TodayController::class, 'index'])->name('today');

    // Chat
    Route::get('/chat', function () {
        return view('chat.gateway');
    })->name('chat');
    Route::get('/chat/ai', [ChatController::class, 'index'])->name('chat.ai');
    Route::post('/chat/send', [ChatController::class, 'send'])->name('chat.send');
    Route::get('/chat/history', [ChatController::class, 'history'])->name('chat.history');
    Route::get('/chat/idle', [ChatController::class, 'checkIdle'])->name('chat.idle');
    Route::post('/chat/end', [ChatController::class, 'endSession'])->name('chat.end');

    // Traveler Community — Rooms
    Route::get('/chat/rooms', [RoomController::class, 'index'])->name('chat.rooms');
    Route::get('/chat/rooms/{room}', [RoomController::class, 'show'])->name('chat.rooms.show');
    Route::post('/chat/rooms/{room}/send', [RoomController::class, 'send'])->name('chat.rooms.send');
    Route::get('/chat/rooms/{room}/history', [RoomController::class, 'history'])->name('chat.rooms.history');

    // Traveler Community — Private chat requests
    Route::get('/chat/requests', [RequestController::class, 'index'])->name('chat.requests');
    Route::post('/chat/requests', [RequestController::class, 'store'])->name('chat.requests.store');
    Route::post('/chat/requests/{id}/accept', [RequestController::class, 'accept'])->name('chat.requests.accept');
    Route::post('/chat/requests/{id}/reject', [RequestController::class, 'reject'])->name('chat.requests.reject');

    // Traveler Community — Private chat
    Route::get('/chat/private/{room}', [PrivateChatController::class, 'show'])->name('chat.private.show');
    Route::post('/chat/private/{room}/send', [PrivateChatController::class, 'send'])->name('chat.private.send');
    Route::get('/chat/private/{room}/history', [PrivateChatController::class, 'history'])->name('chat.private.history');

    // SOS
    Route::post('/sos', [SosController::class, 'send'])->name('sos.send');

    // History
    Route::get('/history', [HistoryController::class, 'index'])->name('history');

    // Upload
    Route::get('/expenses/upload', [ExpenseUploadController::class, 'index'])->name('expenses.upload');
    Route::post('/expenses/upload', [ExpenseUploadController::class, 'store'])->name('expenses.store');
});
