<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\ExpenseController;
use App\Http\Controllers\Api\V1\ItineraryController;
use App\Http\Controllers\Api\V1\MerchantController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\SosController;
use App\Http\Controllers\Api\V1\TransactionController;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});

RateLimiter::for('ai-generate', function (Request $request) {
    return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
});

Route::prefix('v1')->group(function () {

    // ─── Auth ──────────────────────────────────────────
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::get('auth/google/redirect', [AuthController::class, 'googleRedirect']);
    Route::get('auth/google/callback', [AuthController::class, 'googleCallback']);

    // ─── Protected Routes ──────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        // Auth
        Route::post('auth/logout', [AuthController::class, 'logout']);

        // Profile
        Route::get('profile', [ProfileController::class, 'show']);
        Route::put('profile', [ProfileController::class, 'update']);

        // Itinerary
        Route::post('itineraries/generate', [ItineraryController::class, 'generate'])
            ->middleware('throttle:ai-generate');
        Route::get('itineraries/{id}/status', [ItineraryController::class, 'status']);
        Route::get('itineraries', [ItineraryController::class, 'index']);
        Route::get('itineraries/{id}', [ItineraryController::class, 'show']);
        Route::put('itineraries/{id}', [ItineraryController::class, 'update']);
        Route::delete('itineraries/{id}', [ItineraryController::class, 'destroy']);

        // Booking
        Route::post('bookings', [BookingController::class, 'store']);
        Route::get('bookings', [BookingController::class, 'index']);
        Route::get('bookings/{id}', [BookingController::class, 'show']);

        // Merchant Availability
        Route::get('merchants/{id}/availability', [MerchantController::class, 'availability']);

        // Transactions
        Route::get('transactions', [TransactionController::class, 'index']);

        // Expense Uploads
        Route::post('expense-uploads', [ExpenseController::class, 'store']);

        // Notifications
        Route::get('notifications', [NotificationController::class, 'index']);
        Route::put('notifications/{id}/read', [NotificationController::class, 'markRead']);
        Route::put('notifications/read-all', [NotificationController::class, 'markAllRead']);

        // SOS
        Route::post('sos', [SosController::class, 'send']);

        // ─── Merchant API ──────────────────────────────
        Route::prefix('merchant')->middleware('role:merchant')->group(function () {
            Route::get('bookings', function (Request $request) {
                $merchant = $request->user()->merchant;
                if (! $merchant) {
                    return response()->json(['message' => 'Not a merchant.'], 403);
                }

                return response()->json($merchant->bookings()->latest()->paginate(10));
            });
        });
    });

    // ─── Public Webhook ────────────────────────────────
    Route::post('transactions/callback', [TransactionController::class, 'callback']);
});
