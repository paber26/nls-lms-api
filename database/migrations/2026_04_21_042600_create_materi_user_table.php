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
        Schema::create('materi_user', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('user_id')->constrained()->onDelete('cascade');
            $blueprint->foreignId('materi_id')->constrained('materi')->onDelete('cascade');
            $blueprint->timestamps();
            
            // Prevent duplicate entries
            $blueprint->unique(['user_id', 'materi_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materi_user');
    }
};
