<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatTransaksi extends Model
{
    protected $table = 'riwayat_transaksis';

    protected $fillable = [
        'user_id',
        'tanggal',
        'keterangan',
        'jumlah_tabung',
        'pemasukan',
        'pengeluaran',
        'saldo_awal',
        'saldo_akhir',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'jumlah_tabung' => 'integer',
        'pemasukan' => 'decimal:2',
        'pengeluaran' => 'decimal:2',
        'saldo_awal' => 'decimal:2',
        'saldo_akhir' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
