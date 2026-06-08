<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokAkturl extends Model
{
    protected $table = 'stok_akturls';

    protected $fillable = [
        'kapasitas_total',
        'kaleng_isi',
        'kaleng_kosong',
        'kaleng_keluar',
        'total_saldo',
        'harga_jual_default',
        'harga_refill_default',
        'threshold_warning',
    ];

    protected $casts = [
        'kapasitas_total' => 'integer',
        'kaleng_isi' => 'integer',
        'kaleng_kosong' => 'integer',
        'kaleng_keluar' => 'integer',
        'total_saldo' => 'decimal:2',
        'harga_jual_default' => 'decimal:2',
        'harga_refill_default' => 'decimal:2',
        'threshold_warning' => 'integer',
    ];

    /**
     * Get the singleton stock record.
     * Creates the default row if it doesn't exist.
     */
    public static function current(): self
    {
        $stok = self::first();

        if (!$stok) {
            $stok = self::create([
                'kapasitas_total' => 27,
                'kaleng_isi' => 27,
                'kaleng_kosong' => 0,
                'kaleng_keluar' => 0,
                'total_saldo' => 0,
                'harga_jual_default' => 5000,
                'harga_refill_default' => 4000,
                'threshold_warning' => 5,
            ]);
        }

        return $stok;
    }

    /**
     * Check if stock is below warning threshold.
     */
    public function isLowStock(): bool
    {
        return $this->kaleng_isi <= $this->threshold_warning;
    }

    /**
     * Get total physical canisters.
     */
    public function getTotalKalengAttribute(): int
    {
        return $this->kaleng_isi + $this->kaleng_kosong + $this->kaleng_keluar;
    }
}
