@extends('layouts.app')

@section('title', 'Dashboard — Anomali Gas')
@section('header_title', 'Monitoring Stok')

@section('styles')
<style>
    /* STATS GRID */
    .stats-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; margin-bottom: 28px; }
    .stat-card { position: relative; background: rgba(26, 34, 54, 0.5); backdrop-filter: blur(12px); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); padding: 20px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); overflow: hidden; }
    .stat-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; border-radius: var(--radius-lg) var(--radius-lg) 0 0; }
    .stat-card:hover { transform: translateY(-4px); border-color: rgba(99, 102, 241, 0.4); box-shadow: var(--shadow-lg); }
    
    .stat-card--isi::before { background: var(--gradient-emerald); }
    .stat-card--kosong::before { background: var(--gradient-amber); }
    .stat-card--keluar::before { background: var(--gradient-purple); }
    .stat-card--saldo::before { background: var(--gradient-cyan); }
    .stat-card--total::before { background: var(--gradient-indigo); }

    .stat-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
    .stat-icon-wrap { width: 40px; height: 40px; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; font-size: 18px; }
    .stat-card--isi .stat-icon-wrap { background: rgba(52, 211, 153, 0.12); }
    .stat-card--kosong .stat-icon-wrap { background: rgba(251, 191, 36, 0.12); }
    .stat-card--keluar .stat-icon-wrap { background: rgba(168, 85, 247, 0.12); }
    .stat-card--saldo .stat-icon-wrap { background: rgba(34, 211, 238, 0.12); }
    .stat-card--total .stat-icon-wrap { background: rgba(129, 140, 248, 0.12); }

    .stat-label { font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
    .stat-value { font-size: 30px; font-weight: 800; letter-spacing: -1px; line-height: 1.1; margin-bottom: 4px; }
    .stat-card--isi .stat-value { color: var(--accent-emerald); }
    .stat-card--kosong .stat-value { color: var(--accent-amber); }
    .stat-card--keluar .stat-value { color: var(--accent-purple); }
    .stat-card--saldo .stat-value { color: var(--accent-cyan); font-size: 24px; }
    .stat-card--total .stat-value { color: var(--accent-indigo-light); }
    .stat-subtitle { font-size: 11px; color: var(--text-muted); font-weight: 500; }

    .stock-bar-container { margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border-subtle); }
    .stock-bar-label { display: flex; justify-content: space-between; font-size: 10px; color: var(--text-muted); margin-bottom: 6px; }
    .stock-bar { height: 6px; border-radius: 8px; background: rgba(30, 41, 59, 0.8); overflow: hidden; }
    .stock-bar-fill { height: 100%; border-radius: 8px; transition: width 0.8s; }
    .stock-bar-fill--isi { background: var(--gradient-emerald); }
    .stock-bar-fill--kosong { background: var(--gradient-amber); }
    .stock-bar-fill--keluar { background: var(--gradient-purple); }

    /* TABLE */
    .panel-header { display: flex; align-items: center; justify-content: space-between; padding: 20px 24px; border-bottom: 1px solid var(--border-subtle); }
    .panel-title { font-size: 17px; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 10px; }
    .panel-actions { display: flex; gap: 10px; }
    
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table thead th { padding: 14px 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--text-muted); text-align: left; background: rgba(15, 23, 42, 0.5); }
    .data-table tbody td { padding: 14px 20px; font-size: 13px; color: var(--text-secondary); border-bottom: 1px solid var(--border-subtle); }
    .data-table tbody tr:hover { background: rgba(30, 41, 59, 0.4); }

    .badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; font-size: 11px; font-weight: 600; border-radius: 20px; }
    .badge--jual { background: rgba(52, 211, 153, 0.12); color: #6ee7b7; border: 1px solid rgba(52, 211, 153, 0.2); }
    .badge--kembali { background: rgba(168, 85, 247, 0.12); color: #d8b4fe; border: 1px solid rgba(168, 85, 247, 0.2); }
    .badge--refill { background: rgba(251, 191, 36, 0.12); color: #fde68a; border: 1px solid rgba(251, 191, 36, 0.2); }
    .badge--pengeluaran { background: rgba(244, 63, 94, 0.12); color: #fda4af; border: 1px solid rgba(244, 63, 94, 0.2); }
    .badge--pinjam { background: rgba(34, 211, 238, 0.12); color: #a5f3fc; border: 1px solid rgba(34, 211, 238, 0.2); }

    .text-income { color: var(--accent-emerald); font-weight: 600; }
    .text-expense { color: var(--accent-rose); font-weight: 600; }
    .tabular-nums { font-variant-numeric: tabular-nums; }
    .empty-state { padding: 60px 24px; text-align: center; }
    .empty-state-icon { font-size: 48px; margin-bottom: 16px; opacity: 0.4; }
    .pagination-wrapper { padding: 16px 24px; border-top: 1px solid var(--border-subtle); display: flex; justify-content: center; }

    /* MODAL */
    .modal-overlay { position: fixed; inset: 0; background: var(--bg-overlay); backdrop-filter: blur(6px); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px; opacity: 0; visibility: hidden; transition: 0.3s; }
    .modal-overlay.active { opacity: 1; visibility: visible; }
    .modal { background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: var(--radius-xl); width: 100%; max-width: 550px; box-shadow: var(--shadow-lg); transform: scale(0.95); transition: 0.3s; }
    .modal-overlay.active .modal { transform: scale(1); }
    .modal-header { display: flex; align-items: center; justify-content: space-between; padding: 24px 24px 0; }
    .modal-title { font-size: 18px; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 10px; }
    .modal-close { width: 36px; height: 36px; border-radius: 8px; background: rgba(148, 163, 184, 0.08); border: 1px solid var(--border-subtle); color: var(--text-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; }
    .modal-body { padding: 24px; }
    .modal-footer { padding: 0 24px 24px; display: flex; gap: 12px; justify-content: flex-end; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .form-hint { font-size: 12px; color: var(--text-muted); margin-top: 5px; }
    
    @media (max-width: 1024px) { .stats-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 640px) { .stats-grid { grid-template-columns: 1fr; } .form-row { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')
    @if($stok->isLowStock())
        <div class="alert-banner alert-banner--danger animate-in">
            <span>⚠️</span>
            <span>Peringatan: Stok kaleng isi hampir habis! (Sisa: {{ $stok->kaleng_isi }} tabung). Segera lakukan pengisian ulang.</span>
        </div>
    @endif

    {{-- STAT CARDS --}}
    <div class="stats-grid">
        <div class="stat-card stat-card--isi animate-in">
            <div class="stat-header"><span class="stat-label">Kaleng Isi</span><div class="stat-icon-wrap">🟢</div></div>
            <div class="stat-value tabular-nums">{{ $stok->kaleng_isi }}</div>
            <div class="stat-subtitle">tabung siap jual</div>
            <div class="stock-bar-container">
                <div class="stock-bar-label"><span>Kapasitas</span><span>{{ round(($stok->kaleng_isi / $stok->kapasitas_total) * 100) }}%</span></div>
                <div class="stock-bar"><div class="stock-bar-fill stock-bar-fill--isi" style="width: {{ round(($stok->kaleng_isi / $stok->kapasitas_total) * 100) }}%"></div></div>
            </div>
        </div>

        <div class="stat-card stat-card--keluar animate-in">
            <div class="stat-header"><span class="stat-label">Tabung Keluar</span><div class="stat-icon-wrap">🟣</div></div>
            <div class="stat-value tabular-nums">{{ $stok->kaleng_keluar }}</div>
            <div class="stat-subtitle">disewa pelanggan</div>
            <div class="stock-bar-container">
                <div class="stock-bar-label"><span>Kapasitas</span><span>{{ round(($stok->kaleng_keluar / $stok->kapasitas_total) * 100) }}%</span></div>
                <div class="stock-bar"><div class="stock-bar-fill stock-bar-fill--keluar" style="width: {{ round(($stok->kaleng_keluar / $stok->kapasitas_total) * 100) }}%"></div></div>
            </div>
        </div>

        <div class="stat-card stat-card--kosong animate-in">
            <div class="stat-header"><span class="stat-label">Kaleng Kosong</span><div class="stat-icon-wrap">🟡</div></div>
            <div class="stat-value tabular-nums">{{ $stok->kaleng_kosong }}</div>
            <div class="stat-subtitle">perlu direfill</div>
            <div class="stock-bar-container">
                <div class="stock-bar-label"><span>Kapasitas</span><span>{{ round(($stok->kaleng_kosong / $stok->kapasitas_total) * 100) }}%</span></div>
                <div class="stock-bar"><div class="stock-bar-fill stock-bar-fill--kosong" style="width: {{ round(($stok->kaleng_kosong / $stok->kapasitas_total) * 100) }}%"></div></div>
            </div>
        </div>

        <div class="stat-card stat-card--saldo animate-in">
            <div class="stat-header"><span class="stat-label">Total Kas</span><div class="stat-icon-wrap"></div></div>
            <div class="stat-value tabular-nums">Rp {{ number_format((float) $stok->total_saldo, 0, ',', '.') }}</div>
            <div class="stat-subtitle">saldo bersih saat ini</div>
        </div>

        <div class="stat-card stat-card--total animate-in">
            <div class="stat-header"><span class="stat-label">Total Kaleng</span><div class="stat-icon-wrap"></div></div>
            <div class="stat-value tabular-nums">{{ $stok->kapasitas_total }}</div>
            <div class="stat-subtitle">tabung fisik dimiliki</div>
        </div>
    </div>

    {{-- TRANSACTION HISTORY --}}
    <div class="card animate-in">
        <div class="panel-header" style="flex-wrap: wrap; gap: 16px;">
            <h2 class="panel-title"><span>📋</span> Riwayat Aktivitas</h2>
            
            <div class="panel-actions" style="display: flex; align-items: center; flex-wrap: wrap; gap: 10px;">
                <form action="{{ route('dashboard') }}" method="GET" style="display: flex; align-items: center; gap: 10px; background: rgba(30, 41, 59, 0.5); padding: 5px 10px; border-radius: var(--radius-sm); border: 1px solid var(--border-subtle);">
                    <input type="date" name="start_date" class="form-input" style="padding: 6px 10px; width: auto; font-size: 13px;" value="{{ request('start_date') }}" required>
                    <span style="color: var(--text-muted); font-size: 13px;">-</span>
                    <input type="date" name="end_date" class="form-input" style="padding: 6px 10px; width: auto; font-size: 13px;" value="{{ request('end_date') }}" required>
                    <button type="submit" class="btn btn--ghost btn--sm" style="padding: 6px 12px;">Filter</button>
                    @if(request('start_date'))
                        <a href="{{ route('dashboard') }}" class="btn btn--ghost btn--sm" style="padding: 6px 12px; color: var(--accent-rose);">Reset</a>
                    @endif
                </form>

                <a href="{{ route('transaksi.export', ['start_date' => request('start_date'), 'end_date' => request('end_date')]) }}" class="btn btn--ghost btn--sm" style="border-color: var(--accent-emerald); color: var(--accent-emerald);">
                    📥 Export CSV
                </a>

                <button class="btn btn--primary btn--sm" onclick="openModal()">＋ Transaksi Baru</button>
            </div>
        </div>

        @if($riwayat->count() > 0)
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tanggal</th>
                            <th>Admin</th>
                            <th>Tipe Transaksi</th>
                            <th>Jumlah</th>
                            <th>Pemasukan</th>
                            <th>Pengeluaran</th>
                            <th>Catatan</th>
                            @if(auth()->user()->isDeveloper())
                                <th>Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($riwayat as $index => $item)
                            <tr>
                                <td>{{ $riwayat->firstItem() + $index }}</td>
                                <td>{{ $item->tanggal->format('d M Y H:i') }}</td>
                                <td>{{ $item->user ? $item->user->name : 'Sistem' }}</td>
                                <td>
                                    @if($item->keterangan === 'jual') <span class="badge badge--jual">↗ Sewa / Jual</span>
                                    @elseif($item->keterangan === 'kembali') <span class="badge badge--kembali">↺ Kembali</span>
                                    @elseif($item->keterangan === 'refill') <span class="badge badge--refill">↙ Refill </span>
                                    @elseif($item->keterangan === 'pengeluaran_lain') <span class="badge badge--pengeluaran"> Pengeluaran </span>
                                    @elseif($item->keterangan === 'pinjam_modal') <span class="badge badge--pinjam"> Kasbon </span>
                                    @endif
                                </td>
                                <td class="tabular-nums">{{ $item->jumlah_tabung > 0 ? $item->jumlah_tabung . ' tabung' : '-' }}</td>
                                <td class="text-income tabular-nums">{{ (float) $item->pemasukan > 0 ? '+Rp ' . number_format((float) $item->pemasukan, 0, ',', '.') : '—' }}</td>
                                <td class="text-expense tabular-nums">{{ (float) $item->pengeluaran > 0 ? '-Rp ' . number_format((float) $item->pengeluaran, 0, ',', '.') : '—' }}</td>
                                <td>{{ $item->catatan ?: '-' }}</td>
                                @if(auth()->user()->isDeveloper())
                                <td>
                                    <form action="{{ route('transaksi.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini? (Stok & Saldo akan dikembalikan secara otomatis)');" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn--ghost btn--sm" style="color: var(--accent-rose); border-color: var(--accent-rose); padding: 4px 8px; font-size: 11px;">Hapus</button>
                                    </form>
                                </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($riwayat->hasPages())
                <div class="pagination-wrapper">{{ $riwayat->links() }}</div>
            @endif
        @else
            <div class="empty-state">
                <div class="empty-state-icon">📭</div>
                <div>Belum ada transaksi</div>
            </div>
        @endif
    </div>

    {{-- MODAL --}}
    <div class="modal-overlay" id="modal-overlay">
        <div class="modal" id="modal-transaksi">
            <div class="modal-header">
                <h3 class="modal-title"><span>📝</span> Transaksi Baru</h3>
                <button class="modal-close" onclick="closeModal()">✕</button>
            </div>

            <form action="{{ route('transaksi.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    @if($errors->any())
                        <div class="alert-banner alert-banner--error" style="margin-bottom:20px; padding:12px;">
                            <div>@foreach($errors->all() as $error)<div>- {{ $error }}</div>@endforeach</div>
                        </div>
                    @endif

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Tanggal</label>
                            <input type="datetime-local" class="form-input" name="tanggal" value="{{ old('tanggal', now()->format('Y-m-d\TH:i')) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Jenis Transaksi</label>
                            <select class="form-select" id="input-keterangan" name="keterangan" required>
                                <option value="" disabled selected>Pilih jenis...</option>
                                <option value="jual">🟢 Sewa </option>
                                <option value="kembali">🟣 Tabung Kembali</option>
                                <option value="refill">🟡 Refill Kaleng </option>
                                <option value="pengeluaran_lain">💸 Pengeluaran Lainnya </option>
                                <option value="pinjam_modal">💳 Kasbon </option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row" id="row-jumlah">
                        <div class="form-group">
                            <label class="form-label">Jumlah Tabung</label>
                            <input type="number" class="form-input" id="input-jumlah" name="jumlah_tabung" min="1" max="{{ $stok->kapasitas_total }}" placeholder="cth: 5">
                            <div class="form-hint" id="hint-jumlah">Max tergantung jenis transaksi</div>
                        </div>
                        <div class="form-group" id="col-nominal" style="display:none;">
                            <label class="form-label" id="label-nominal">Nominal (Rp)</label>
                            <input type="number" class="form-input" id="input-nominal" name="nominal" min="0" step="100" placeholder="Wajib diisi">
                            <div class="form-hint" id="hint-nominal">Khusus untuk pengeluaran/kasbon</div>
                        </div>
                    </div>

                    <div class="form-group" id="col-catatan">
                        <label class="form-label">Catatan Keterangan</label>
                        <input type="text" class="form-input" id="input-catatan" name="catatan" placeholder="Opsional untuk sewa, Wajib untuk kasbon/pengeluaran">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn--ghost" onclick="closeModal()">Batal</button>
                    <button type="submit" class="btn btn--primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    const overlay = document.getElementById('modal-overlay');
    function openModal() { overlay.classList.add('active'); }
    function closeModal() { overlay.classList.remove('active'); }
    overlay.addEventListener('click', e => { if (e.target === overlay) closeModal(); });

    const selectKet = document.getElementById('input-keterangan');
    const inputJumlah = document.getElementById('input-jumlah');
    const colNominal = document.getElementById('col-nominal');
    const inputNominal = document.getElementById('input-nominal');
    const rowJumlah = document.getElementById('row-jumlah');
    const hintJumlah = document.getElementById('hint-jumlah');
    const inputCatatan = document.getElementById('input-catatan');

    selectKet.addEventListener('change', function () {
        // Reset defaults
        inputJumlah.required = false;
        inputNominal.required = false;
        inputCatatan.required = false;
        rowJumlah.style.display = 'grid';
        colNominal.style.display = 'none'; // hidden by default for jual, kembali, refill
        inputJumlah.parentElement.style.display = 'block';

        if (this.value === 'jual') {
            inputJumlah.required = true;
            rowJumlah.style.display = 'block'; // Only show jumlah
            hintJumlah.textContent = `Kaleng Isi: {{ $stok->kaleng_isi }}`;
        } else if (this.value === 'kembali') {
            inputJumlah.required = true;
            rowJumlah.style.display = 'block';
            hintJumlah.textContent = `Tabung di pelanggan: {{ $stok->kaleng_keluar }}`;
        } else if (this.value === 'refill') {
            inputJumlah.required = true;
            rowJumlah.style.display = 'block';
            hintJumlah.textContent = `Tabung kosong: {{ $stok->kaleng_kosong }} `;
        } else if (['pengeluaran_lain', 'pinjam_modal'].includes(this.value)) {
            inputJumlah.parentElement.style.display = 'none';
            colNominal.style.display = 'block';
            rowJumlah.style.display = 'block';
            inputNominal.required = true;
            inputCatatan.required = true;
            document.getElementById('label-nominal').textContent = this.value === 'pengeluaran_lain' ? 'Nominal Pengeluaran (Rp)' : 'Nominal Kasbon (Rp)';
        }
    });

    @if($errors->any()) openModal(); @endif
</script>
@endsection
