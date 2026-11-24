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
        Schema::table('pasars', fn (Blueprint $table) => $table->string('satuan_jual', 8)->nullable());
        Schema::create('retail_orders', function (Blueprint $table) {
            $table->id();
            // Preserve snapshots and participant history after a listing is removed.
            $table->unsignedBigInteger('pasar_id');
            $table->unsignedBigInteger('buyer_id')->index();
            $table->unsignedBigInteger('seller_id')->index();
            $table->string('nama_ikan');
            $table->string('satuan_jual', 8);
            $table->unsignedInteger('harga_satuan');
            $table->decimal('jumlah', 9, 3);
            $table->unsignedBigInteger('total_harga');
            $table->string('status_bayar')->default('Belum Bayar');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retail_orders');
        Schema::table('pasars', fn (Blueprint $table) => $table->dropColumn('satuan_jual'));
    }
};
