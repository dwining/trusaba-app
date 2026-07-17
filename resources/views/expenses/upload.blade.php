@extends('layouts.app', ['showNav' => false])
@section('title', 'TruSaba · Upload Bukti')
@section('content')

<div class="app-header">
    <a class="icon-btn" href="{{ route('history', ['tab' => 'tx']) }}" aria-label="Kembali">
        <svg viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
    </a>
    <div class="title-block">
        <p class="eyebrow">Pengeluaran</p>
        <h1>Upload bukti</h1>
    </div>
</div>

<div class="app-body no-nav has-sticky">
    <form method="POST" action="{{ route('expenses.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="pad">
            <div class="field">
                <label class="field-label">Foto / file bukti</label>
                <div class="upload-zone" id="uploadZone" role="button" tabindex="0">
                    <svg viewBox="0 0 24 24"><path d="M12 16V6"/><path d="M8 10l4-4 4 4"/><path d="M4 18h16"/></svg>
                    <p style="font-weight:600;font-size:14px">Ketuk untuk unggah</p>
                    <p class="caption" style="margin-top:4px">JPG, PNG, atau PDF · max 5 MB</p>
                    <p class="small" id="fileName" style="margin-top:8px;color:var(--accent-hex);font-weight:550" hidden></p>
                </div>
                <input type="file" name="file" id="fileInput" accept="image/*,.pdf" hidden required />
                @error('file')<p class="caption" style="color:var(--danger);margin-top:4px">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label class="field-label" for="amount">Nominal <span class="req">*</span></label>
                <input class="input mono" id="amount" name="amount" type="text" inputmode="numeric" placeholder="Rp 0" required />
                @error('amount')<p class="caption" style="color:var(--danger)">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label class="field-label" for="cat">Kategori <span class="req">*</span></label>
                <select class="select" id="cat" name="category" required>
                    <option value="">Pilih kategori</option>
                    <option>Akomodasi</option>
                    <option>Makan</option>
                    <option>Wisata</option>
                    <option>Transport</option>
                    <option>Oleh-oleh</option>
                    <option>Lainnya</option>
                </select>
            </div>

            <div class="field">
                <label class="field-label" for="note">Catatan (opsional)</label>
                <textarea class="textarea" id="note" name="description" placeholder="Contoh: makan siang Bebek Bengil"></textarea>
            </div>
        </div>

        <div class="sticky-cta">
            <button type="submit" class="btn btn-primary btn-block">Simpan</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.getElementById('uploadZone').addEventListener('click', function() { document.getElementById('fileInput').click(); });
document.getElementById('fileInput').addEventListener('change', function() {
    if (this.files[0]) {
        var el = document.getElementById('fileName');
        el.hidden = false;
        el.textContent = this.files[0].name;
        document.getElementById('uploadZone').classList.add('drag');
    }
});
</script>
@endpush
@endsection
