<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stok_akturls', function (Blueprint $table) {
            $table->id();
            $table->integer('kapasitas_total')->default(27);
            $table->integer('kaleng_isi')->default(27);
            $table->integer('kaleng_kosong')->default(0);
            $table->integer('kaleng_keluar')->default(0);
            $table->decimal('total_saldo', 15, 2)->default(0);
            $table->decimal('harga_jual_default', 15, 2)->default(5000);
            $table->decimal('harga_refill_default', 15, 2)->default(4000);
            $table->integer('threshold_warning')->default(5);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stok_akturls');
    }
};
