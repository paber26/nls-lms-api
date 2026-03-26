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
        Schema::table('tryout_komponen', function (Blueprint $table) {
            $table->integer('durasi_menit')->default(0)->after('urutan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tryout_komponen', function (Blueprint $table) {
            $table->dropColumn('durasi_menit');
        });
    }
};
