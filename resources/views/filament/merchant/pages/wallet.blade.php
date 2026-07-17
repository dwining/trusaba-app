<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <p class="text-sm text-gray-500">Saldo Tersedia</p>
            <p class="text-3xl font-bold text-amber-600">Rp {{ number_format($balance, 0, ',', '.') }}</p>
        </div>
    </div>

    <h3 class="text-lg font-semibold mb-3">Riwayat Transaksi Wallet</h3>
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="p-3 text-left">Tanggal</th>
                    <th class="p-3 text-left">Tipe</th>
                    <th class="p-3 text-right">Jumlah</th>
                    <th class="p-3 text-left">Deskripsi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $tx)
                <tr class="border-t">
                    <td class="p-3">{{ $tx->created_at->format('d M H:i') }}</td>
                    <td class="p-3">
                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $tx->type === 'credit' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $tx->type === 'credit' ? 'Masuk' : 'Keluar' }}
                        </span>
                    </td>
                    <td class="p-3 text-right font-mono">Rp {{ number_format($tx->amount, 0, ',', '.') }}</td>
                    <td class="p-3 text-gray-500">{{ $tx->description }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="p-6 text-center text-gray-400">Belum ada transaksi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <h3 class="text-lg font-semibold mt-6 mb-3">Riwayat Withdrawal</h3>
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="p-3 text-left">Tanggal</th>
                    <th class="p-3 text-right">Jumlah</th>
                    <th class="p-3 text-left">Bank</th>
                    <th class="p-3 text-left">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($withdrawals as $w)
                <tr class="border-t">
                    <td class="p-3">{{ $w->created_at->format('d M Y') }}</td>
                    <td class="p-3 text-right font-mono">Rp {{ number_format($w->amount, 0, ',', '.') }}</td>
                    <td class="p-3">{{ $w->bank_name }}</td>
                    <td class="p-3">
                        @php
                            $statusMap = [
                                'pending' => ['bg-yellow-100 text-yellow-700', 'Pending'],
                                'processed' => ['bg-green-100 text-green-700', 'Diproses'],
                                'rejected' => ['bg-red-100 text-red-700', 'Ditolak'],
                            ];
                        @endphp
                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $statusMap[$w->status][0] }}">
                            {{ $statusMap[$w->status][1] }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="p-6 text-center text-gray-400">Belum ada withdrawal.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
