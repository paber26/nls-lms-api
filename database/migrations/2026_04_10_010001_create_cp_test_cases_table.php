<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cp_test_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('problem_id')->constrained('cp_problems')->cascadeOnDelete();
            $table->text('input')->nullable();
            $table->text('expected_output')->nullable();
            $table->boolean('is_hidden')->default(true); // If true, it's a hidden test case used for evaluating. If false, it's a sample test case for users.
            $table->integer('points')->default(0); // If partial scoring is needed
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cp_test_cases');
    }
};
