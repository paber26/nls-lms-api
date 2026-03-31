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
        Schema::create('attempt_komponen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('attempt')->onDelete('cascade');
            $table->foreignId('komponen_id')->constrained('komponen')->onDelete('cascade');
            $table->dateTime('mulai')->nullable();
            $table->dateTime('selesai')->nullable();
            $table->enum('status', ['ongoing', 'finished'])->default('ongoing');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attempt_komponen');
    }
};
