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
        Schema::create('materi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('modul_id');
            $table->string('judul');
            $table->string('tipe')->default('article'); // 'article', 'video'
            $table->longText('konten')->nullable();
            $table->string('videoUrl')->nullable();
            $table->text('deskripsi')->nullable();
            $table->integer('durasi')->default(0);
            $table->integer('urutan')->default(0);
            $table->timestamps();

            // $table->foreign('modul_id')->references('id')->on('modul')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materi');
    }
};
