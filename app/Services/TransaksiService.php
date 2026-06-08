<?php

namespace App\Services;

use App\Models\RiwayatTransaksi;
use App\Models\StokAkturl;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TransaksiService
{
    /**
     * Process a 'jual' (sell/rent) transaction.
     * - Decreases kaleng_isi
     * - Increases kaleng_keluar (rented)
     * - Adds pemasukan to total_saldo based on default price
     */
    public function jual(int $jumlah, string $tanggal, ?int $userId, ?string $catatan = null): RiwayatTransaksi
    {
        return DB::transaction(function () use ($jumlah, $tanggal, $userId, $catatan) {
            $stok = StokAkturl::lockForUpdate()->first() ?? StokAkturl::current();

            if ($jumlah <= 0) {
                throw new InvalidArgumentException('Jumlah tabung harus lebih dari 0.');
            }

            if ($stok->kaleng_isi < $jumlah) {
                throw new InvalidArgumentException(
                    "Stok kaleng isi tidak mencukupi. Tersedia: {$stok->kaleng_isi}, diminta: {$jumlah}."
                );
            }

            // Nominal is strictly taken from default settings
            $nominal = $jumlah * (float) $stok->harga_jual_default;

            $saldoAwal = (float) $stok->total_saldo;
            $saldoAkhir = $saldoAwal + $nominal;

            $stok->kaleng_isi -= $jumlah;
            $stok->kaleng_keluar += $jumlah;
            $stok->total_saldo = $saldoAkhir;

            $this->validateInvariant($stok);
            $stok->save();

            return RiwayatTransaksi::create([
                'user_id' => $userId,
                'tanggal' => $tanggal,
                'keterangan' => 'jual',
                'jumlah_tabung' => $jumlah,
                'pemasukan' => $nominal,
                'pengeluaran' => 0,
                'saldo_awal' => $saldoAwal,
                'saldo_akhir' => $saldoAkhir,
                'catatan' => $catatan,
            ]);
        });
    }

    /**
     * Process a 'kembali' (return) transaction.
     * - Decreases kaleng_keluar
     * - Increases kaleng_kosong
     * - No financial impact
     */
    public function kembali(int $jumlah, string $tanggal, ?int $userId, ?string $catatan = null): RiwayatTransaksi
    {
        return DB::transaction(function () use ($jumlah, $tanggal, $userId, $catatan) {
            $stok = StokAkturl::lockForUpdate()->first() ?? StokAkturl::current();

            if ($jumlah <= 0) {
                throw new InvalidArgumentException('Jumlah tabung harus lebih dari 0.');
            }

            if ($stok->kaleng_keluar < $jumlah) {
                throw new InvalidArgumentException(
                    "Tabung keluar yang belum kembali hanya: {$stok->kaleng_keluar}, diminta: {$jumlah}."
                );
            }

            $saldoAwal = (float) $stok->total_saldo;

            $stok->kaleng_keluar -= $jumlah;
            $stok->kaleng_kosong += $jumlah;

            $this->validateInvariant($stok);
            $stok->save();

            return RiwayatTransaksi::create([
                'user_id' => $userId,
                'tanggal' => $tanggal,
                'keterangan' => 'kembali',
                'jumlah_tabung' => $jumlah,
                'pemasukan' => 0,
                'pengeluaran' => 0,
                'saldo_awal' => $saldoAwal,
                'saldo_akhir' => $saldoAwal,
                'catatan' => $catatan,
            ]);
        });
    }

    /**
     * Process a 'refill' transaction.
     * - Decreases kaleng_kosong
     * - Increases kaleng_isi
     * - NO financial impact (total_saldo is unchanged), cost is tracked via 'pengeluaran_lain'
     */
    public function refill(int $jumlah, string $tanggal, ?int $userId, ?string $catatan = null): RiwayatTransaksi
    {
        return DB::transaction(function () use ($jumlah, $tanggal, $userId, $catatan) {
            $stok = StokAkturl::lockForUpdate()->first() ?? StokAkturl::current();

            if ($jumlah <= 0) {
                throw new InvalidArgumentException('Jumlah tabung harus lebih dari 0.');
            }

            if ($stok->kaleng_kosong < $jumlah) {
                throw new InvalidArgumentException(
                    "Stok kaleng kosong tidak mencukupi. Tersedia: {$stok->kaleng_kosong}, diminta: {$jumlah}."
                );
            }

            $saldoAwal = (float) $stok->total_saldo;
            // No saldo deduction per user request.

            $stok->kaleng_kosong -= $jumlah;
            $stok->kaleng_isi += $jumlah;
            // total_saldo remains the same

            $this->validateInvariant($stok);
            $stok->save();

            return RiwayatTransaksi::create([
                'user_id' => $userId,
                'tanggal' => $tanggal,
                'keterangan' => 'refill',
                'jumlah_tabung' => $jumlah,
                'pemasukan' => 0,
                'pengeluaran' => 0, // Recorded as 0
                'saldo_awal' => $saldoAwal,
                'saldo_akhir' => $saldoAwal,
                'catatan' => $catatan,
            ]);
        });
    }

    /**
     * Process an expense (pengeluaran_lain).
     */
    public function pengeluaranLain(float $nominal, string $tanggal, ?int $userId, string $catatan): RiwayatTransaksi
    {
        return DB::transaction(function () use ($nominal, $tanggal, $userId, $catatan) {
            $stok = StokAkturl::lockForUpdate()->first() ?? StokAkturl::current();

            if ($nominal <= 0) {
                throw new InvalidArgumentException('Nominal pengeluaran harus lebih dari 0.');
            }

            if (empty(trim($catatan))) {
                throw new InvalidArgumentException('Catatan/Keterangan wajib diisi untuk pengeluaran lain.');
            }

            $saldoAwal = (float) $stok->total_saldo;
            $saldoAkhir = $saldoAwal - $nominal;

            $stok->total_saldo = $saldoAkhir;
            $stok->save();

            return RiwayatTransaksi::create([
                'user_id' => $userId,
                'tanggal' => $tanggal,
                'keterangan' => 'pengeluaran_lain',
                'jumlah_tabung' => 0,
                'pemasukan' => 0,
                'pengeluaran' => $nominal,
                'saldo_awal' => $saldoAwal,
                'saldo_akhir' => $saldoAkhir,
                'catatan' => $catatan,
            ]);
        });
    }

    /**
     * Process an admin cash advance (pinjam_modal / kasbon).
     */
    public function pinjamModal(float $nominal, string $tanggal, ?int $userId, string $catatan): RiwayatTransaksi
    {
        return DB::transaction(function () use ($nominal, $tanggal, $userId, $catatan) {
            $stok = StokAkturl::lockForUpdate()->first() ?? StokAkturl::current();

            if ($nominal <= 0) {
                throw new InvalidArgumentException('Nominal pinjaman harus lebih dari 0.');
            }

            $saldoAwal = (float) $stok->total_saldo;
            $saldoAkhir = $saldoAwal - $nominal;

            $stok->total_saldo = $saldoAkhir;
            $stok->save();

            return RiwayatTransaksi::create([
                'user_id' => $userId,
                'tanggal' => $tanggal,
                'keterangan' => 'pinjam_modal',
                'jumlah_tabung' => 0,
                'pemasukan' => 0,
                'pengeluaran' => $nominal,
                'saldo_awal' => $saldoAwal,
                'saldo_akhir' => $saldoAkhir,
                'catatan' => $catatan,
            ]);
        });
    }

    /**
     * Validate the physical canister capacity invariant.
     */
    protected function validateInvariant(StokAkturl $stok): void
    {
        $totalFisik = $stok->kaleng_isi + $stok->kaleng_kosong + $stok->kaleng_keluar;
        if ($totalFisik !== $stok->kapasitas_total) {
            throw new InvalidArgumentException(
                "Pelanggaran batas kapasitas: total kaleng (isi+kosong+keluar) harus berjumlah {$stok->kapasitas_total}. Saat ini: {$totalFisik}."
            );
        }
    }
}
