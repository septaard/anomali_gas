@extends('layouts.app')

@section('title', 'Anomali Gas')
@section('header_title', 'Pengaturan Sistem')

@section('styles')
<style>
    .settings-container { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
    .card { background: rgba(26, 34, 54, 0.5); backdrop-filter: blur(12px); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); padding: 24px; }
    .card-title { font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 10px; margin-bottom: 20px; border-bottom: 1px solid var(--border-subtle); padding-bottom: 16px; }
    
    .form-group { margin-bottom: 20px; }
    .form-label { display: block; font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 7px; }
    .input-wrapper { position: relative; display: flex; align-items: center; }
    .input-prefix { position: absolute; left: 14px; color: var(--text-muted); font-size: 14px; font-weight: 500; }
    .form-input { width: 100%; padding: 11px 14px; font-size: 14px; font-family: var(--font-sans); color: var(--text-primary); background: var(--bg-input); border: 1px solid var(--border-default); border-radius: var(--radius-sm); outline: none; transition: 0.25s; }
    .form-input.has-prefix { padding-left: 40px; }
    .form-input:focus { border-color: var(--accent-indigo); box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15); }
    .form-hint { font-size: 12px; color: var(--text-muted); margin-top: 6px; }

    .warning-box { background: rgba(251, 191, 36, 0.1); border: 1px solid rgba(251, 191, 36, 0.3); border-radius: var(--radius-md); padding: 16px; margin-bottom: 24px; font-size: 13px; color: #fde68a; display: flex; gap: 12px; align-items: flex-start; }
    .warning-icon { font-size: 20px; }

    @media (max-width: 768px) { .settings-container { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')
<form action="{{ route('settings.update') }}" method="POST">
    @csrf
    <div class="settings-container">
        
        {{-- HARGA DEFAULT --}}
        <div class="card animate-in" style="animation-delay: 0.1s;">
            <h2 class="card-title"><span>💸</span> Harga PerKaleng</h2>
            <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 20px;">
                Harga ini akan otomatis digunakan oleh sistem ketika admin melakukan transaksi sewa tanpa perlu menginput ulang nominal secara manual.
            </p>

            <div class="form-group">
                <label class="form-label">Harga Sewa </label>
                <div class="input-wrapper">
                    <span class="input-prefix">Rp</span>
                    <input type="number" name="harga_jual_default" class="form-input has-prefix" value="{{ old('harga_jual_default', (int) $stok->harga_jual_default) }}" required min="0" step="100">
                </div>
                <div class="form-hint">Digunakan saat transaksi. </div>
            </div>

            <div class="form-group">
                <label class="form-label">Harga Modal Refill </label>
                <div class="input-wrapper">
                    <span class="input-prefix">Rp</span>
                    <input type="number" name="harga_refill_default" class="form-input has-prefix" value="{{ old('harga_refill_default', (int) $stok->harga_refill_default) }}" required min="0" step="100">
                </div>
                <div class="form-hint">Biaya operasional saat isi ulang gas kosong. .</div>
            </div>
        </div>

        {{-- KAPASITAS & WARNING --}}
        <div class="card animate-in" style="animation-delay: 0.2s;">
            <h2 class="card-title"><span>📦</span> Kapasitas & Monitoring</h2>
            
            <div class="warning-box">
                <div class="warning-icon">⚠️</div>
                <div>
                    <strong>Penting:</strong> Jika Anda menambah kapasitas total, sistem akan otomatis menambahkan selisihnya sebagai <strong>Kaleng Kosong</strong> yang baru (karena tabung baru diasumsikan kosong). Jika Anda mengurangi, pastikan jumlah kaleng kosong Anda cukup untuk dikurangi.
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Total Kapasitas Kaleng</label>
                <input type="number" name="kapasitas_total" class="form-input" value="{{ old('kapasitas_total', $stok->kapasitas_total) }}" required min="1">
                <div class="form-hint">Saat ini: <strong>{{ $stok->kapasitas_total }} tabung</strong></div>
            </div>

            <div class="form-group">
                <label class="form-label">Batas Peringatan Stok Sedikit</label>
                <input type="number" name="threshold_warning" class="form-input" value="{{ old('threshold_warning', $stok->threshold_warning) }}" required min="1">
                <div class="form-hint">Banner merah akan muncul jika Kaleng Isi mencapai batas ini.</div>
            </div>
        </div>

    </div>

    <div style="margin-top: 24px; text-align: right;" class="animate-in" style="animation-delay: 0.3s;">
        <button type="submit" class="btn btn--primary" style="padding: 12px 32px; font-size: 15px;">💾 Simpan Pengaturan</button>
    </div>
</form>
@endsection
