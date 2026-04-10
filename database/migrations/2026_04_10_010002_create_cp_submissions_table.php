<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cp_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('problem_id')->constrained('cp_problems')->cascadeOnDelete();
            $table->longText('source_code');
            $table->integer('language_id'); // Judge0 Language ID
            $table->string('verdict')->nullable(); // e.g. Accepted, Wrong Answer, Time Limit Exceeded
            $table->double('execution_time')->nullable(); // seconds
            $table->double('memory_used')->nullable(); // kilobytes
            $table->json('judge0_response')->nullable(); // Dump of Judge0 final response
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cp_submissions');
    }
};
