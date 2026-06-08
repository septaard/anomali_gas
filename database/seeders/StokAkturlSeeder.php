<?php

namespace Database\Seeders;

use App\Models\StokAkturl;
use Illuminate\Database\Seeder;

class StokAkturlSeeder extends Seeder
{
    /**
     * Seed the singleton stock record.
     */
    public function run(): void
    {
        StokAkturl::firstOrCreate(
            ['id' => 1],
            [
                'kapasitas_total' => 27,
                'kaleng_isi' => 27,
                'kaleng_kosong' => 0,
                'kaleng_keluar' => 0,
                'total_saldo' => 0,
                'harga_jual_default' => 5000,
                'harga_refill_default' => 4000,
                'threshold_warning' => 5,
            ]
        );
    }
}
