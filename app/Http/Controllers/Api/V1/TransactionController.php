<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index()
    {
        return response()->json(
            Auth::user()->transactions()->latest()->paginate(10)
        );
    }

    public function callback(Request $request)
    {
        $validated = $request->validate([
            'order_id' => ['required', 'string'],
            'transaction_status' => ['required', 'string'],
        ]);

        $bookingId = str_replace('BOOK-', '', $validated['order_id']);
        $transaction = Transaction::where('booking_id', $bookingId)->first();

        if (! $transaction) {
            return response()->json(['message' => 'Transaction not found.'], 404);
        }

        $statusMap = [
            'settlement' => 'paid',
            'capture' => 'paid',
            'pending' => 'pending',
            'deny' => 'failed',
            'cancel' => 'failed',
            'expire' => 'failed',
            'refund' => 'refunded',
        ];

        $transaction->update([
            'status' => $statusMap[$validated['transaction_status']] ?? 'pending',
            'paid_at' => in_array($validated['transaction_status'], ['settlement', 'capture']) ? now() : null,
            'gateway_trx_id' => $request->input('transaction_id'),
        ]);

        return response()->json(['message' => 'Callback processed.']);
    }
}
