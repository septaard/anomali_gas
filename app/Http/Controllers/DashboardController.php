<?php

namespace App\Http\Controllers;

use App\Models\RiwayatTransaksi;
use App\Models\StokAkturl;
use App\Services\TransaksiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class DashboardController extends Controller
{
    protected TransaksiService $transaksiService;

    public function __construct(TransaksiService $transaksiService)
    {
        $this->transaksiService = $transaksiService;
    }

    /**
     * Display the dashboard with current stock and transaction history.
     */
    public function index(Request $request)
    {
        $stok = StokAkturl::current();
        
        $query = RiwayatTransaksi::with('user');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }

        $riwayat = $query->orderBy('tanggal', 'desc')->orderBy('id', 'desc')->paginate(15);

        return view('dashboard', compact('stok', 'riwayat'));
    }

    /**
     * Export transaction history to CSV based on date filter.
     */
    public function exportCsv(Request $request)
    {
        $query = RiwayatTransaksi::with('user')->orderBy('tanggal', 'desc');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }

        $riwayat = $query->get();

        $filename = "transaksi_anomali_gas_" . date('Y-m-d_H-i-s') . ".csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Tanggal', 'Admin', 'Tipe Transaksi', 'Jumlah Tabung', 'Pemasukan', 'Pengeluaran', 'Catatan'];

        $callback = function() use($riwayat, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($riwayat as $item) {
                $row['Tanggal']  = $item->tanggal->format('Y-m-d H:i:s');
                $row['Admin']    = $item->user ? $item->user->name : 'Sistem';
                $row['Tipe Transaksi']  = $item->keterangan;
                $row['Jumlah Tabung']  = $item->jumlah_tabung;
                $row['Pemasukan']  = $item->pemasukan;
                $row['Pengeluaran']  = $item->pengeluaran;
                $row['Catatan']  = $item->catatan ?? '';

                fputcsv($file, array($row['Tanggal'], $row['Admin'], $row['Tipe Transaksi'], $row['Jumlah Tabung'], $row['Pemasukan'], $row['Pengeluaran'], $row['Catatan']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Process a new transaction.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'required|in:jual,kembali,refill,pengeluaran_lain,pinjam_modal',
            'jumlah_tabung' => 'nullable|integer|min:0',
            'nominal' => 'nullable|numeric|min:0',
            'catatan' => 'nullable|string|max:255',
        ], [
            'tanggal.required' => 'Tanggal wajib diisi.',
            'keterangan.required' => 'Jenis transaksi wajib dipilih.',
            'keterangan.in' => 'Jenis transaksi tidak valid.',
            'jumlah_tabung.integer' => 'Jumlah tabung harus berupa angka.',
            'jumlah_tabung.min' => 'Jumlah tabung tidak boleh negatif.',
            'nominal.numeric' => 'Nominal harus berupa angka.',
            'nominal.min' => 'Nominal tidak boleh negatif.',
            'catatan.max' => 'Catatan maksimal 255 karakter.',
        ]);

        $userId = Auth::id();
        $jumlah = (int) ($validated['jumlah_tabung'] ?? 0);
        $nominal = isset($validated['nominal']) && $validated['nominal'] !== '' ? (float) $validated['nominal'] : null;
        $catatan = $validated['catatan'] ?? null;
        $tanggal = $validated['tanggal'];
        $keterangan = $validated['keterangan'];

        try {
            switch ($keterangan) {
                case 'jual':
                    $this->transaksiService->jual($jumlah, $tanggal, $userId, $catatan);
                    $message = 'Transaksi penjualan berhasil dicatat.';
                    break;
                case 'kembali':
                    $this->transaksiService->kembali($jumlah, $tanggal, $userId, $catatan);
                    $message = 'Pengembalian tabung sewa berhasil dicatat.';
                    break;
                case 'refill':
                    $this->transaksiService->refill($jumlah, $tanggal, $userId, $catatan);
                    $message = 'Transaksi pengisian ulang (refill) berhasil dicatat.';
                    break;
                case 'pengeluaran_lain':
                    $this->transaksiService->pengeluaranLain((float) $nominal, $tanggal, $userId, $catatan ?? '-');
                    $message = 'Pengeluaran lain berhasil dicatat.';
                    break;
                case 'pinjam_modal':
                    $this->transaksiService->pinjamModal((float) $nominal, $tanggal, $userId, $catatan ?? '-');
                    $message = 'Pinjaman modal (kasbon) berhasil dicatat.';
                    break;
            }

            return redirect()->route('dashboard')->with('success', $message ?? 'Berhasil');
        } catch (InvalidArgumentException $e) {
            return redirect()->route('dashboard')->with('error', $e->getMessage());
        }
    }

    /**
     * Delete a transaction (Developer only).
     */
    public function destroy($id)
    {
        if (!auth()->user()->isDeveloper()) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $this->transaksiService->destroy($id);
            return redirect()->route('dashboard')->with('success', 'Data transaksi berhasil dihapus dan direvert.');
        } catch (\Exception $e) {
            return redirect()->route('dashboard')->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}
