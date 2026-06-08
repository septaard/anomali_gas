<?php

namespace App\Http\Controllers;

use App\Models\StokAkturl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SettingsController extends Controller
{
    public function index()
    {
        $stok = StokAkturl::current();
        return view('settings', compact('stok'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'harga_jual_default' => 'required|numeric|min:0',
            'harga_refill_default' => 'required|numeric|min:0',
            'kapasitas_total' => 'required|integer|min:1',
            'threshold_warning' => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $stok = StokAkturl::lockForUpdate()->first() ?? StokAkturl::current();

                $oldKapasitas = $stok->kapasitas_total;
                $newKapasitas = (int) $validated['kapasitas_total'];

                if ($newKapasitas !== $oldKapasitas) {
                    $selisih = $newKapasitas - $oldKapasitas;

                    if ($selisih > 0) {
                        // Capacity increased -> Add to kaleng_kosong
                        $stok->kaleng_kosong += $selisih;
                    } else {
                        // Capacity decreased -> Remove from kaleng_kosong
                        $selisihAbsolut = abs($selisih);
                        if ($stok->kaleng_kosong < $selisihAbsolut) {
                            throw new InvalidArgumentException(
                                "Tidak bisa mengurangi kapasitas! Hanya tersedia {$stok->kaleng_kosong} kaleng kosong, sementara Anda mencoba mengurangi {$selisihAbsolut} kaleng."
                            );
                        }
                        $stok->kaleng_kosong -= $selisihAbsolut;
                    }
                    $stok->kapasitas_total = $newKapasitas;
                }

                $stok->harga_jual_default = $validated['harga_jual_default'];
                $stok->harga_refill_default = $validated['harga_refill_default'];
                $stok->threshold_warning = $validated['threshold_warning'];

                $stok->save();
            });

            return redirect()->route('settings.index')->with('success', 'Pengaturan berhasil diperbarui!');
        } catch (InvalidArgumentException $e) {
            return redirect()->route('settings.index')->with('error', $e->getMessage());
        }
    }
}
