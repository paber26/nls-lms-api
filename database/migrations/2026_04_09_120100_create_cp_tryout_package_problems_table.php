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
        Schema::create('cp_tryout_package_problems', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cp_tryout_package_id')
                ->constrained('cp_tryout_packages')
                ->cascadeOnDelete();
            $table->foreignUuid('cf_problem_id')
                ->constrained('cf_problems')
                ->cascadeOnDelete();
            $table->unsignedInteger('urutan')->nullable();
            $table->timestamps();

            $table->unique(
                ['cp_tryout_package_id', 'cf_problem_id'],
                'cp_tryout_package_problem_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cp_tryout_package_problems');
    }
};

