<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseUploadController extends Controller
{
    public function index()
    {
        $uploads = Auth::user()->expenseUploads()->latest()->get();

        return view('expenses.upload', compact('uploads'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'itinerary_id' => ['nullable', 'exists:itineraries,id'],
            'booking_id' => ['nullable', 'exists:bookings,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:255'],
            'category' => ['required', 'in:Akomodasi,Makan,Wisata,Transport,Oleh-oleh,Lainnya'],
        ], [
            'file.required' => 'File bukti wajib diunggah.',
            'file.mimes' => 'File harus JPG, PNG, atau PDF.',
            'file.max' => 'File maksimal 5 MB.',
            'amount.required' => 'Nominal wajib diisi.',
            'category.required' => 'Kategori wajib dipilih.',
        ]);

        $path = $request->file('file')->store('expenses', 'public');

        Auth::user()->expenseUploads()->create([
            'file_path' => $path,
            'itinerary_id' => $validated['itinerary_id'] ?? null,
            'booking_id' => $validated['booking_id'] ?? null,
            'amount' => $validated['amount'],
            'description' => $validated['description'] ?? null,
            'is_processed' => false,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Bukti transaksi berhasil disimpan.']);
        }

        return redirect()->route('history', ['tab' => 'tx'])->with('toast', 'Bukti transaksi berhasil disimpan.');
    }
}
