<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cp_problems', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description_html')->nullable();
            $table->double('time_limit')->default(1.0); // seconds
            $table->double('memory_limit')->default(256.0); // MB
            $table->integer('points')->default(100);
            $table->foreignId('komponen_id')->nullable()->references('id')->on('mapel')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cp_problems');
    }
};
